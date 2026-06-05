<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Audit Logs Report - FEEDTAN</title>
    <style> 
         body { 
             font-family: 'Helvetica', 'Arial', sans-serif; 
             color: #333; 
             line-height: 1.4; 
             margin: 0; 
             padding: 0; 
         } 
         .container { 
             width: 100%; 
             padding: 15px; 
         } 
         .header { 
             text-align: center; 
             border-bottom: 2px solid #16a34a; 
             padding-bottom: 15px; 
             margin-bottom: 20px; 
         } 
         .logo { 
             font-size: 22px; 
             font-weight: 900; 
             color: #16a34a; 
             text-transform: uppercase; 
             letter-spacing: 1px; 
         } 
         .sub-header { 
             font-size: 11px; 
             color: #16a34a; 
             font-weight: bold; 
             margin-top: 2px; 
             text-transform: uppercase; 
         } 
         .report-title { 
             font-size: 18px; 
             margin-top: 8px; 
             color: #111; 
             font-weight: 900; 
             background: #f3f4f6; 
             padding: 5px 15px; 
             display: inline-block; 
             border-radius: 4px; 
         }
         .summary-section {
             display: flex;
             justify-content: space-between;
             margin-bottom: 20px;
         }
         .report-info, .summary-stats {
             border: 1px solid #e5e7eb;
             border-radius: 6px;
             padding: 12px;
             background: #fff;
             width: 48%;
         }
         .summary-stats {
             background: linear-gradient(to right, #f0fdf4, #ffffff); 
             border: 1px solid #bcf0da;
         }
         .label {
             font-weight: 800; 
             color: #4b5563; 
             text-transform: uppercase; 
             font-size: 10px; 
         }
         .value {
             font-weight: 700; 
             color: #111; 
             font-size: 12px; 
         }
         table {
             width: 100%;
             border-collapse: collapse;
             margin-top: 10px;
         }
         th, td {
             border: 1px solid #e5e7eb;
             padding: 8px;
             text-align: left;
             word-wrap: break-word;
         }
         th {
             background-color: #16a34a;
             color: white;
             font-weight: 800;
             font-size: 10px;
             text-transform: uppercase;
         }
         td {
             font-size: 10px;
         }
         .action-login {
             color: #166534;
             font-weight: bold;
             background: #dcfce7;
             padding: 2px 6px;
             border-radius: 4px;
             font-size: 9px;
             text-transform: uppercase;
         }
         .action-logout {
             color: #4b5563;
             font-weight: bold;
             background: #f3f4f6;
             padding: 2px 6px;
             border-radius: 4px;
             font-size: 9px;
             text-transform: uppercase;
         }
         .action-login-failed {
             color: #991b1b;
             font-weight: bold;
             background: #fee2e2;
             padding: 2px 6px;
             border-radius: 4px;
             font-size: 9px;
             text-transform: uppercase;
         }
         .action-other {
             color: #1e40af;
             font-weight: bold;
             background: #dbeafe;
             padding: 2px 6px;
             border-radius: 4px;
             font-size: 9px;
             text-transform: uppercase;
         }
         .text-right {
             text-align: right;
         }
         .footer {
             margin-top: 30px;
             text-align: center;
             font-size: 10px;
             color: #6b7280;
             border-top: 1px dashed #e5e7eb;
             padding-top: 15px;
         }
     </style> 
</head>
<body>
     <div class="container"> 
         <div class="header"> 
             <div class="logo" style="font-size: 18px;">FeedTan Community Microfinance Group</div>
             <div class="sub-header" style="font-size: 10px; margin-top: 4px;">P.O.Box 7744, Ushirika Sokoine Road, Moshi, Kilimanjaro, Tanzania</div>
             <div class="sub-header">DIGITAL PAYMENT SYSTEM</div> 
             <div class="report-title">AUDIT LOGS REPORT</div> 
         </div> 
        
         <div class="summary-section">
            <div class="report-info">
                <div class="label">Report Generated On:</div>
                <div class="value"><?php echo date('l, d F Y H:i:s'); ?></div>
                
                <div class="label" style="margin-top: 8px;">Total Records:</div>
                <div class="value"><?php echo count($audits); ?> log entries</div>
                
                <?php if (!empty($startDate) || !empty($endDate)): ?>
                    <div class="label" style="margin-top: 8px;">Date Range:</div>
                    <div class="value">
                        <?php if (!empty($startDate)) { echo htmlspecialchars($startDate); } ?>
                        <?php if (!empty($startDate) && !empty($endDate)) { echo ' to '; } ?>
                        <?php if (!empty($endDate)) { echo htmlspecialchars($endDate); } ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="summary-stats">
                <div class="label" style="color: #16a34a;">Log Type Summary</div>
                <div class="value" style="margin-top: 5px;">
                    <span style="color: #166534;">Login:</span> <?php echo $loginCount; ?>
                </div>
                <div class="value">
                    <span style="color: #4b5563;">Logout:</span> <?php echo $logoutCount; ?>
                </div>
                <div class="value">
                    <span style="color: #991b1b;">Failed Login:</span> <?php echo $failedLoginCount; ?>
                </div>
                <div class="value">
                    <span style="color: #1e40af;">Other:</span> <?php echo $otherCount; ?>
                </div>
            </div>
        </div>

    <table>
        <thead>
            <tr>
                <th>Date & Time</th>
                <th>User</th>
                <th>Action</th>
                <th>Details</th>
                <th>IP Address</th>
                <th>Location</th>
                <th>Device</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($audits as $audit): ?>
                <?php
                    // Determine action class without match()
                    $actionClass = 'action-other';
                    if ($audit['action'] === 'login') $actionClass = 'action-login';
                    elseif ($audit['action'] === 'logout') $actionClass = 'action-logout';
                    elseif ($audit['action'] === 'login_failed') $actionClass = 'action-login-failed';
                    
                    // Format date
                    $formattedDate = 'N/A';
                    if (isset($audit['created_at'])) {
                        $formattedDate = \Carbon\Carbon::parse($audit['created_at'])->format('Y-m-d H:i:s');
                    }
                    
                    // Format location
                    $locationParts = [];
                    if (!empty($audit['city'])) $locationParts[] = $audit['city'];
                    if (!empty($audit['country'])) $locationParts[] = $audit['country'];
                    $location = !empty($locationParts) ? implode(', ', $locationParts) : 'N/A';
                    
                    // Format device
                    $deviceParts = [];
                    if (!empty($audit['device_type'])) $deviceParts[] = $audit['device_type'];
                    $deviceLine1 = !empty($deviceParts) ? implode(', ', $deviceParts) : 'N/A';
                    
                    $deviceLine2Parts = [];
                    if (!empty($audit['device_browser'])) $deviceLine2Parts[] = $audit['device_browser'];
                    if (!empty($audit['device_platform'])) $deviceLine2Parts[] = $audit['device_platform'];
                    $deviceLine2 = !empty($deviceLine2Parts) ? implode(' / ', $deviceLine2Parts) : '';
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($formattedDate); ?></td>
                    <td>
                        <?php echo htmlspecialchars($audit['user_name']); ?><br>
                        <span style="color: #6b7280; font-size:9px;"><?php echo htmlspecialchars($audit['user_email']); ?></span>
                    </td>
                    <td>
                        <span class="<?php echo $actionClass; ?>"><?php echo ucwords(str_replace('_', ' ', htmlspecialchars($audit['action']))); ?></span>
                    </td>
                    <td><?php echo htmlspecialchars($audit['details'] ?? 'N/A'); ?></td>
                    <td><?php echo htmlspecialchars($audit['ip_address'] ?? 'N/A'); ?></td>
                    <td><?php echo htmlspecialchars($location); ?></td>
                    <td>
                        <?php echo htmlspecialchars($deviceLine1); ?><br>
                        <?php if (!empty($deviceLine2)): ?>
                            <span style="color: #6b7280; font-size:9px;"><?php echo htmlspecialchars($deviceLine2); ?></span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <?php if (!empty($audits)): ?>
        <div class="footer">
            <strong>FEEDTAN DIGITAL PAYMENT SYSTEM</strong><br>
            Powered by FeedTan Team<br>
            <span style="color: #16a34a;">www.feedtancmg.org • service@feedtancmg.org</span><br>
            <div style="margin-top: 10px; font-size: 8px; color: #9ca3af;">
                This document is electronically generated and verified by FEEDTAN DIGITAL PAYMENT SYSTEM.
            </div>
        </div>
    <?php endif; ?>
     </div> 
</body>
</html>
