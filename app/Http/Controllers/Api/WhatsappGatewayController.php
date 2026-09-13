<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SmsDevice;
use App\Models\WhatsappMessage;
use App\Models\WhatsappMessageLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class WhatsappGatewayController extends Controller
{
    /**
     * GET /api/gateway/whatsapp/commands
     * Device polls for pending WhatsApp requests assigned to it.
     * Auth: device.auth middleware
     */
    public function commands(Request $request)
    {
        /** @var SmsDevice $device */
        $device = $request->attributes->get('device');
        $limit = min(20, (int) $request->input('limit', 10));

        $messages = WhatsappMessage::where('device_id', $device->id)
            ->whereIn('status', ['PENDING','QUEUED'])
            ->orderBy('requested_at')
            ->limit($limit)
            ->get()
            ->map(function($m){
                return [
                    'uuid' => $m->uuid,
                    'recipient_phone' => $m->recipient_phone,
                    'message' => $m->message,
                    'has_attachment' => !empty($m->attachment_path),
                    'attachment_name' => $m->attachment_name,
                    'attachment_mime' => $m->attachment_mime,
                    'attachment_size' => $m->attachment_size,
                    'attachment_url' => $m->attachment_path ? url('/api/gateway/attachments/'.$m->uuid) : null,
                    'status' => $m->status,
                    'requested_at' => $m->requested_at?->toIso8601String(),
                ];
            });

        // Optionally mark as DELIVERED_TO_DEVICE when fetched? Keep PENDING until device confirms via received
        return response()->json([
            'success' => true,
            'device' => $device->device_code,
            'count' => $messages->count(),
            'data' => $messages,
        ]);
    }

    /**
     * POST /api/gateway/whatsapp/{uuid}/received
     * Device confirms it has stored request locally (WorkManager queue)
     */
    public function received(Request $request, $uuid)
    {
        /** @var SmsDevice $device */
        $device = $request->attributes->get('device');
        $msg = WhatsappMessage::where('uuid', $uuid)->where('device_id', $device->id)->firstOrFail();
        if(!in_array($msg->status, ['PENDING','QUEUED'])){
            return response()->json(['success'=>false,'message'=>'Invalid status transition from '.$msg->status], 422);
        }
        $msg->update(['status'=>'DELIVERED_TO_DEVICE','received_by_device_at'=>now()]);
        WhatsappMessageLog::create([
            'whatsapp_message_id'=>$msg->id,
            'device_id'=>$device->id,
            'action'=>'Received by device',
            'details'=>'Stored locally on '.$device->device_code,
        ]);
        return response()->json(['success'=>true,'status'=>$msg->status]);
    }

    /**
     * POST /api/gateway/whatsapp/{uuid}/opened
     * Device opened WhatsApp via Intent (ACTION_VIEW / ACTION_SEND)
     */
    public function opened(Request $request, $uuid)
    {
        /** @var SmsDevice $device */
        $device = $request->attributes->get('device');
        $msg = WhatsappMessage::where('uuid', $uuid)->where('device_id', $device->id)->firstOrFail();
        $msg->update(['status'=>'OPENED','opened_whatsapp_at'=>now()]);
        WhatsappMessageLog::create([
            'whatsapp_message_id'=>$msg->id,
            'device_id'=>$device->id,
            'action'=>'WhatsApp Open Requested',
            'details'=>'Intent launched for '.$msg->recipient_phone,
            'meta'=>['has_attachment'=>!empty($msg->attachment_path)],
        ]);
        return response()->json(['success'=>true,'status'=>$msg->status]);
    }

    /**
     * POST /api/gateway/whatsapp/{uuid}/status
     * Generic status update from device
     * Body: { status: SENT|FAILED|USER_ACTION_REQUIRED|OPENING|... , failure_reason? }
     */
    public function updateStatus(Request $request, $uuid)
    {
        $request->validate([
            'status' => 'required|string|in:PENDING,QUEUED,DELIVERED_TO_DEVICE,OPENING,OPENED,USER_ACTION_REQUIRED,SENT,FAILED,CANCELLED,EXPIRED',
            'failure_reason' => 'nullable|string|max:1000',
        ]);
        /** @var SmsDevice $device */
        $device = $request->attributes->get('device');
        $msg = WhatsappMessage::where('uuid', $uuid)->where('device_id', $device->id)->firstOrFail();

        $newStatus = $request->status;
        // Prevent false SENT: allow device to mark SENT only via completed endpoint or explicit user action
        $update = ['status'=>$newStatus];
        if($newStatus==='SENT'){
            $update['sent_at']=now();
            $update['opened_whatsapp_at']=$update['opened_whatsapp_at']??now();
        }
        if($newStatus==='FAILED'){
            $update['failed_at']=now();
            $update['failure_reason']=$request->failure_reason;
        }
        if($newStatus==='OPENED'){
            $update['opened_whatsapp_at']=now();
        }
        $msg->update($update);
        WhatsappMessageLog::create([
            'whatsapp_message_id'=>$msg->id,
            'device_id'=>$device->id,
            'action'=>'Status: '.$newStatus,
            'details'=>$request->failure_reason ?? 'Updated via gateway',
        ]);
        return response()->json(['success'=>true,'status'=>$msg->status]);
    }

    /**
     * POST /api/gateway/whatsapp/{uuid}/completed
     * User tapped YES — MARK SENT in WhatsApp after returning
     */
    public function completed(Request $request, $uuid)
    {
        /** @var SmsDevice $device */
        $device = $request->attributes->get('device');
        $msg = WhatsappMessage::where('uuid', $uuid)->where('device_id', $device->id)->firstOrFail();
        $msg->update(['status'=>'SENT','sent_at'=>now()]);
        WhatsappMessageLog::create([
            'whatsapp_message_id'=>$msg->id,
            'device_id'=>$device->id,
            'action'=>'User Marked Sent',
            'details'=>'User tapped YES — MARK SENT after WhatsApp',
        ]);
        return response()->json(['success'=>true,'status'=>'SENT']);
    }

    /**
     * GET /api/gateway/attachments/{uuid}
     * Authenticated download via FileProvider on Android side
     */
    public function attachment(Request $request, $uuid)
    {
        /** @var SmsDevice $device */
        $device = $request->attributes->get('device');
        $msg = WhatsappMessage::where('uuid', $uuid)->where('device_id', $device->id)->firstOrFail();
        if(empty($msg->attachment_path) || !Storage::disk('local')->exists($msg->attachment_path)){
            return response()->json(['success'=>false,'message'=>'Attachment not found'], 404);
        }
        WhatsappMessageLog::create([
            'whatsapp_message_id'=>$msg->id,
            'device_id'=>$device->id,
            'action'=>'Attachment Download Started',
            'details'=>$msg->attachment_name,
        ]);
        // Stream private file with correct mime and filename, no public URL
        return Storage::disk('local')->download($msg->attachment_path, $msg->attachment_name, [
            'Content-Type' => $msg->attachment_mime ?? 'application/octet-stream',
        ]);
    }

    /**
     * POST /api/gateway/whatsapp/{uuid}/cancelled
     * User tapped NO — KEEP PENDING or device reports cancel
     */
    public function cancelled(Request $request, $uuid)
    {
        /** @var SmsDevice $device */
        $device = $request->attributes->get('device');
        $msg = WhatsappMessage::where('uuid', $uuid)->where('device_id', $device->id)->firstOrFail();
        // Keep as USER_ACTION_REQUIRED or revert to PENDING
        $msg->update(['status'=>'USER_ACTION_REQUIRED']);
        WhatsappMessageLog::create([
            'whatsapp_message_id'=>$msg->id,
            'device_id'=>$device->id,
            'action'=>'User Cancelled / Keep Pending',
            'details'=>'User tapped NO — KEEP PENDING',
        ]);
        return response()->json(['success'=>true,'status'=>$msg->status]);
    }
}
