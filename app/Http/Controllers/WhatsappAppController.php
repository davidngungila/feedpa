<?php

namespace App\Http\Controllers;

use App\Models\SmsDevice;
use App\Models\SmsLocation;
use App\Models\WhatsappMessage;
use App\Models\WhatsappMessageLog;
use App\Models\WhatsappTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class WhatsappAppController extends Controller
{
    public function dashboard()
    {
        $stats = [
            'pending' => WhatsappMessage::whereIn('status', ['PENDING','QUEUED'])->count(),
            'delivered' => WhatsappMessage::where('status','DELIVERED_TO_DEVICE')->count(),
            'opened' => WhatsappMessage::whereIn('status',['OPENED','USER_ACTION_REQUIRED','OPENING'])->count(),
            'sent' => WhatsappMessage::where('status','SENT')->count(),
            'failed' => WhatsappMessage::where('status','FAILED')->count(),
            'today' => WhatsappMessage::whereDate('requested_at', today())->count(),
        ];
        $recent = WhatsappMessage::with(['device','creator'])->latest('requested_at')->limit(10)->get();
        $devices = SmsDevice::with('location')->get()->map(fn($d)=>[
            'id'=>$d->id,
            'code'=>$d->device_code,
            'name'=>$d->name,
            'location'=>$d->location?->name,
            'status'=>$d->status,
            'online'=>$d->isOnline(),
            'battery'=>$d->battery_level,
            'network'=>$d->network_type,
            'pending'=> WhatsappMessage::where('device_id',$d->id)->whereIn('status',['PENDING','QUEUED'])->count(),
            'last_seen'=>$d->last_heartbeat_at,
        ]);
        return view('whatsapp-app.dashboard', compact('stats','recent','devices'));
    }

    public function outbox(Request $request)
    {
        $q = WhatsappMessage::with(['device','creator'])->latest('requested_at');
        if ($request->filled('status')) $q->where('status',$request->status);
        if ($request->filled('device_id')) $q->where('device_id',$request->device_id);
        if ($request->filled('search')) {
            $s=$request->search;
            $q->where(fn($qq)=>$qq->where('recipient_phone','like',"%$s%")->orWhere('message','like',"%$s%")->orWhere('uuid','like',"%$s%"));
        }
        if ($request->filled('has_attachment')) {
            if($request->has_attachment==='1') $q->whereNotNull('attachment_path');
            else $q->whereNull('attachment_path');
        }
        $messages = $q->paginate(20)->withQueryString();
        $devices = SmsDevice::select('id','device_code','name')->get();
        return view('whatsapp-app.outbox', compact('messages','devices'));
    }

    // Alias for backwards compat: inbox/sent etc filter by status
    public function index(Request $request){ return $this->outbox($request); }

    public function create()
    {
        $devices = SmsDevice::where('status','ACTIVE')->get(); // only ACTIVE
        // For UI show ONLINE/OFFLINE but allow queue if OFFLINE with warning
        $allDevices = SmsDevice::with('location')->get();
        $templates = WhatsappTemplate::where('is_active',true)->get();
        return view('whatsapp-app.create', compact('devices','allDevices','templates'));
    }

    public function store(Request $request)
    {
        // Robust pre-check for any upload issue (covers php.ini, nginx client_max_body_size, tmp missing etc)
        $hasFile = $request->hasFile('attachment');
        $rawHasAttachment = $request->has('attachment') || isset($_FILES['attachment']);
        $phpError = $_FILES['attachment']['error'] ?? null;
        if($rawHasAttachment && !$hasFile){
            // File was sent but Laravel doesn't see it as valid file (likely post_max_size / client_max_body_size exceeded or no tmp)
            $code = is_int($phpError) ? $phpError : 0;
            $map = [
                UPLOAD_ERR_INI_SIZE => 'File too large for server (upload_max_filesize='.ini_get('upload_max_filesize').').',
                UPLOAD_ERR_FORM_SIZE => 'File too large (form limit).',
                UPLOAD_ERR_PARTIAL => 'File only partially uploaded, try again.',
                UPLOAD_ERR_NO_FILE => 'No file uploaded.',
                UPLOAD_ERR_NO_TMP_DIR => 'Server temp folder missing.',
                UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk (permission).',
                UPLOAD_ERR_EXTENSION => 'Upload blocked by extension.',
            ];
            $msg = $map[$code] ?? 'Upload failed before reaching app (php error '.$code.', post_max_size='.ini_get('post_max_size').'). Try smaller file (<5MB) or check nginx client_max_body_size.';
            \Illuminate\Support\Facades\Log::error('WhatsApp attachment hasFile false but raw present', ['code'=>$code, 'phpError'=>$phpError, '_FILES'=>$_FILES['attachment'] ?? null, 'contentLength'=>$_SERVER['CONTENT_LENGTH'] ?? null]);
            return back()->withErrors(['attachment'=>$msg])->withInput();
        }
        if($hasFile){
            $f = $request->file('attachment');
            if(!$f->isValid()){
                $code = $f->getError();
                $map = [
                    UPLOAD_ERR_INI_SIZE => 'File too large for server (upload_max_filesize='.ini_get('upload_max_filesize').').',
                    UPLOAD_ERR_FORM_SIZE => 'File too large (form limit).',
                    UPLOAD_ERR_PARTIAL => 'File only partially uploaded, try again.',
                    UPLOAD_ERR_NO_FILE => 'No file uploaded.',
                    UPLOAD_ERR_NO_TMP_DIR => 'Server temp folder missing.',
                    UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk (permission).',
                    UPLOAD_ERR_EXTENSION => 'Upload blocked by extension.',
                ];
                $msg = $map[$code] ?? 'Upload failed (code '.$code.').';
                \Illuminate\Support\Facades\Log::error('WhatsApp attachment upload invalid', ['code'=>$code, 'name'=>$f->getClientOriginalName(), 'size'=>$f->getSize(), 'mime'=>$f->getMimeType()]);
                return back()->withErrors(['attachment'=>$msg.' Please try a smaller file or different format.'])->withInput();
            }
            // Also check mime/extension early for clearer message
            $ext = strtolower($f->getClientOriginalExtension());
            $allowedExt = ['pdf','doc','docx','xls','xlsx','jpg','jpeg','png','txt','csv','zip'];
            if($ext && !in_array($ext, $allowedExt)){
                // Let validator handle, but give early hint
                \Illuminate\Support\Facades\Log::warning('WhatsApp attachment extension not allowed', ['ext'=>$ext, 'name'=>$f->getClientOriginalName()]);
            }
        }

        $request->validate([
            'device_id' => 'required|exists:sms_devices,id',
            'recipient_phone' => 'required|string|max:20',
            'message' => 'nullable|string|max:4000',
            'attachment' => 'nullable|file|max:25600|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,txt,csv,zip',
            'template_id' => 'nullable|exists:whatsapp_templates,id',
        ]);

        // At least message or attachment required (allow attachment-only where appropriate)
        if(empty($request->message) && !$request->hasFile('attachment')){
            return back()->withErrors(['message'=>'Message or attachment required'])->withInput();
        }

        $device = SmsDevice::findOrFail($request->device_id);
        // normalize phone to +255 format for WhatsApp
        $phone = preg_replace('/\D/', '', $request->recipient_phone);
        if(str_starts_with($phone,'0')) $phone='255'.substr($phone,1);
        if(!str_starts_with($phone,'255')) $phone='255'.$phone;
        $phone='+'. $phone;

        // Template variable replacement if template selected
        $finalMessage = $request->message;
        if($request->filled('template_id')){
            $tpl = WhatsappTemplate::find($request->template_id);
            if($tpl){
                // For now simple replacement; future: pass vars from request
                $vars = $request->input('vars', []);
                $finalMessage = $tpl->render($vars + ['message' => $request->message ?? '']);
                if(empty($finalMessage)) $finalMessage = $tpl->content;
            }
        }

        $uuid = (string) Str::uuid();
        $data = [
            'uuid' => $uuid,
            'device_id' => $device->id,
            'created_by' => Auth::id(),
            'recipient_phone' => $phone,
            'message' => $finalMessage,
            'status' => 'PENDING',
            'requested_at' => now(),
        ];

        if($request->hasFile('attachment')){
            $file = $request->file('attachment');
            if(!$file->isValid()){
                return back()->withErrors(['attachment'=>'Attachment is not valid (error '.$file->getError().'). Please re-select file.'])->withInput();
            }
            try {
                // Ensure private directory exists and is writable
                $dir = WhatsappMessage::attachmentDir($uuid);
                $fullDir = storage_path('app/private/'.$dir);
                if(!is_dir($fullDir)){
                    @mkdir($fullDir, 0755, true);
                }
                $original = $file->getClientOriginalName();
                $base = pathinfo($original, PATHINFO_FILENAME);
                $slug = Str::slug($base);
                if(empty($slug)) $slug = 'file';
                $ext = strtolower($file->getClientOriginalExtension());
                if(empty($ext)) $ext = $file->extension() ?: 'bin';
                $safeName = $slug.'.'.$ext;
                // Ensure unique if exists (should not, uuid dir is unique)
                $path = $file->storeAs($dir, $safeName, 'local'); // private storage/app/private
                if(!$path){
                    throw new \Exception('storeAs returned false - check storage/app/private permissions');
                }
                $data['attachment_path'] = $path;
                $data['attachment_name'] = $original;
                $data['attachment_mime'] = $file->getMimeType() ?: $file->getClientMimeType();
                $data['attachment_size'] = $file->getSize();
                \Illuminate\Support\Facades\Log::info('WhatsApp attachment stored', ['uuid'=>$uuid, 'path'=>$path, 'size'=>$data['attachment_size']]);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('WhatsApp attachment store failed', ['uuid'=>$uuid, 'error'=>$e->getMessage(), 'trace'=>$e->getTraceAsString()]);
                return back()->withErrors(['attachment'=>'Failed to store attachment: '.$e->getMessage().'. Check storage permissions.'])->withInput();
            }
        }

        // Device ONLINE check - allow queue but warn
        $isOnline = $device->isOnline();
        if(!$isOnline){
            $data['status'] = 'QUEUED'; // queued until device online
        }

        $msg = WhatsappMessage::create($data);
        WhatsappMessageLog::create([
            'whatsapp_message_id' => $msg->id,
            'device_id' => $device->id,
            'action' => 'Request Created',
            'details' => 'Created by '.(Auth::user()->name ?? 'system').' for '.$phone,
            'meta' => ['online'=>$isOnline, 'has_attachment'=>!empty($data['attachment_path'])],
        ]);

        return redirect()->route('whatsapp-app.outbox')->with('success', 'WhatsApp request created ('.$msg->uuid.') for '.$device->device_code.($isOnline?'':' — device OFFLINE, queued.'));
    }

    public function show(Request $request, WhatsappMessage $whatsappMessage)
    {
        $whatsappMessage->load(['device.location','creator','logs']);
        if($request->expectsJson() || $request->wantsJson() || $request->header('Accept')==='application/json' || $request->header('X-Requested-With')==='XMLHttpRequest'){
            return response()->json([
                'uuid'=>$whatsappMessage->uuid,
                'recipient_phone'=>$whatsappMessage->recipient_phone,
                'message'=>$whatsappMessage->message,
                'attachment_name'=>$whatsappMessage->attachment_name,
                'attachment_mime'=>$whatsappMessage->attachment_mime,
                'attachment_size'=>$whatsappMessage->attachment_size,
                'status'=>$whatsappMessage->status,
                'requested_at'=>$whatsappMessage->requested_at?->toIso8601String(),
                'device_code'=>$whatsappMessage->device?->device_code,
            ]);
        }
        return view('whatsapp-app.show', ['msg'=>$whatsappMessage]);
    }

    public function cancel(WhatsappMessage $whatsappMessage)
    {
        if(!in_array($whatsappMessage->status, ['PENDING','QUEUED'])){
            return back()->with('error','Only PENDING/QUEUED can be cancelled.');
        }
        $whatsappMessage->update(['status'=>'CANCELLED']);
        WhatsappMessageLog::create([
            'whatsapp_message_id'=>$whatsappMessage->id,
            'action'=>'Cancelled',
            'details'=>'Cancelled by '.Auth::user()->name,
        ]);
        return back()->with('success','Request cancelled.');
    }

    public function devices()
    {
        $devices = SmsDevice::with(['location'])->withCount([
            'whatsappMessages as pending_whatsapp' => fn($q)=>$q->whereIn('status',['PENDING','QUEUED'])
        ])->get();
        // Actually need relationship defined on SmsDevice for whatsappMessages - add if missing
        return view('whatsapp-app.devices', compact('devices'));
    }

    public function logs(WhatsappMessage $whatsappMessage)
    {
        $logs = $whatsappMessage->logs()->with('device')->latest()->paginate(30);
        return view('whatsapp-app.logs', compact('whatsappMessage','logs'));
    }

    public function templates()
    {
        $templates = WhatsappTemplate::latest()->paginate(20);
        return view('whatsapp-app.templates', compact('templates'));
    }

    public function storeTemplate(Request $request)
    {
        $request->validate(['name'=>'required|string|max:100','code'=>'required|string|max:50|unique:whatsapp_templates,code','content'=>'required|string|max:4000']);
        // extract variables like {customer_name}
        preg_match_all('/\{([a-z_]+)\}/i', $request->content, $m);
        $vars = array_unique($m[1] ?? []);
        WhatsappTemplate::create([
            'name'=>$request->name,
            'code'=>$request->code,
            'content'=>$request->content,
            'variables'=>$vars,
            'created_by'=>Auth::id(),
        ]);
        return back()->with('success','Template created.');
    }
}
