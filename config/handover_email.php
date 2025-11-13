<?php
require_once __DIR__ . '/email.php';

if (!function_exists('sendHandoverConfirmationEmail')) {
    /**
     * Send handover confirmation email to lecturer
     *
     * @param array $handover Handover data array
     * @param array|null $equipment Equipment data array (optional)
     * @return bool True if email was sent successfully, false otherwise
     */
    function sendHandoverConfirmationEmail(array $handover, ?array $equipment = null) {
        if (empty($handover['lecturer_email'])) {
            error_log("No lecturer email provided for handover ID: " . ($handover['handoverID'] ?? 'unknown'));
            return false;
        }

        $equipment = $equipment ?? [];

        $lecturer_name = $handover['lecturer_name'] ?? 'Lecturer';
        $equipment_name = $equipment['equipment_name'] ?? $handover['equipment_name'] ?? 'Equipment';
        $equipment_id = $equipment['equipment_id'] ?? $handover['equipment_id'] ?? 'N/A';
        $pickup_date = !empty($handover['pickup_date']) ? date('d M Y', strtotime($handover['pickup_date'])) : 'N/A';
        $return_date = !empty($handover['return_date']) ? date('d M Y', strtotime($handover['return_date'])) : 'Not specified';

        $subject = 'Equipment Handover Confirmation - ' . $equipment_name;

        $message = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #2c3e50; color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0; }
                .content { background-color: #f9f9f9; padding: 20px; border: 1px solid #ddd; border-top: none; }
                .info-box { background-color: white; padding: 15px; margin: 15px 0; border-left: 4px solid #3498db; }
                .info-label { font-weight: bold; color: #555; margin-bottom: 5px; }
                .info-value { color: #333; }
                .footer { text-align: center; padding: 20px; color: #777; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Equipment Handover Confirmation</h1>
                </div>
                <div class='content'>
                    <p>Dear $lecturer_name,</p>

                    <p>This email confirms that you have successfully received the following equipment:</p>

                    <div class='info-box'>
                        <div class='info-label'>Equipment ID:</div>
                        <div class='info-value'>$equipment_id</div>
                    </div>

                    <div class='info-box'>
                        <div class='info-label'>Equipment Name:</div>
                        <div class='info-value'>$equipment_name</div>
                    </div>

                    <div class='info-box'>
                        <div class='info-label'>Pickup Date:</div>
                        <div class='info-value'>$pickup_date</div>
                    </div>

                    <div class='info-box'>
                        <div class='info-label'>Expected Return Date:</div>
                        <div class='info-value'>$return_date</div>
                    </div>

                    <p><strong>Important Reminders:</strong></p>
                    <ul>
                        <li>Please take proper care of the equipment</li>
                        <li>Report any issues or damages immediately to the IT department</li>
                        <li>Return the equipment in the same condition as received</li>
                        <li>Ensure all accessories and documentation are included upon return</li>
                    </ul>

                    <p>If you have any questions or concerns, please contact the IT department at <a href='mailto:itventory@unikl.edu.my'>itventory@unikl.edu.my</a>.</p>

                    <p>Thank you for your cooperation.</p>

                    <p>Best regards,<br>
                    <strong>RCMP ITventory System</strong><br>
                    IT Department, UniKL RCMP</p>
                </div>
                <div class='footer'>
                    <p>This is an automated email. Please do not reply to this message.</p>
                    <p>&copy; " . date('Y') . " RCMP ITventory System - University Kuala Lumpur</p>
                </div>
            </div>
        </body>
        </html>
        ";

        return sendEmail($handover['lecturer_email'], $subject, $message);
    }
}
