<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/handover_email.php';
require_once __DIR__ . '/../auth/session.php';

// Check if user is logged in
if (!isLoggedIn()) {
    setSessionMessage('error', 'Please log in to access this page.');
    header('Location: ../auth/login.php');
    exit();
}

$user = getUserData();
$handover = null;
$equipment = null;
$handoverStaffName = null;
$returnStaffName = null;
$error = '';
$success = '';

// Handle handover action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'handover') {
    $equipment_id = isset($_POST['equipment_id']) ? trim($_POST['equipment_id']) : '';
    $agree_terms = isset($_POST['agreeTerms']) && $_POST['agreeTerms'] === 'on';
    
    if (empty($equipment_id)) {
        $error = 'Equipment ID is required.';
    } elseif (!$agree_terms) {
        $error = 'You must agree to the terms and conditions to proceed.';
    } elseif (!$user || empty($user['staff_id'])) {
        $error = 'Staff ID is required. Please log in again.';
    } else {
        try {
            $conn = getDBConnection();
            
            // Check if equipment table exists
            $equipment_table_check = $conn->query("SHOW TABLES LIKE 'equipment'");
            $has_equipment_table = $equipment_table_check && $equipment_table_check->num_rows > 0;
            
            if ($has_equipment_table) {
                // New structure: Check equipment table for handover status
                $check_stmt = $conn->prepare("SELECT handover_status, staff_name FROM equipment WHERE equipment_id = ?");
                $check_stmt->bind_param("s", $equipment_id);
                $check_stmt->execute();
                $check_result = $check_stmt->get_result();
                
                if ($check_result->num_rows === 1) {
                    $equipment_data = $check_result->fetch_assoc();
                    
                    if (empty($equipment_data['staff_name'])) {
                        $error = 'No handover found for this equipment.';
                    } elseif ($equipment_data['handover_status'] !== 'pending') {
                        $error = 'This handover has already been processed.';
                    } else {
                        // Start transaction
                        $conn->autocommit(FALSE);
                        
                        try {
                            // Update equipment table: Set handover status to 'picked_up' and handover_staff
                            $update_stmt = $conn->prepare("UPDATE equipment SET handover_status = 'picked_up', handover_staff = ? WHERE equipment_id = ?");
                            $update_stmt->bind_param("ss", $user['staff_id'], $equipment_id);
                            
                            if (!$update_stmt->execute()) {
                                throw new Exception("Failed to update handover: " . $update_stmt->error);
                            }
                            $update_stmt->close();
                            
                            // Commit transaction
                            $conn->commit();
                            $conn->autocommit(TRUE);
                            
                            // Fetch updated equipment data for email
                            $equipment_stmt = $conn->prepare("SELECT e.*, c.category_name as category FROM equipment e LEFT JOIN categories c ON e.category_id = c.id WHERE e.equipment_id = ?");
                            $equipment_stmt->bind_param("s", $equipment_id);
                            $equipment_stmt->execute();
                            $equipment_result = $equipment_stmt->get_result();
                            $updated_equipment = $equipment_result->fetch_assoc();
                            $equipment_stmt->close();
                            
                            // Prepare handover data for email function (backward compatibility)
                            $updated_handover = [
                                'equipment_id' => $updated_equipment['equipment_id'],
                                'equipment_name' => $updated_equipment['equipment_name'],
                                'lecturer_id' => $updated_equipment['staff_id'],
                                'lecturer_name' => $updated_equipment['staff_name'],
                                'lecturer_email' => $updated_equipment['staff_email'],
                                'pickup_date' => $updated_equipment['pickup_date'],
                                'return_date' => $updated_equipment['return_date'],
                                'handoverStat' => $updated_equipment['handover_status']
                            ];
                            
                            // Send email notification to lecturer
                            if ($updated_handover && $updated_equipment) {
                                $email_sent = sendHandoverConfirmationEmail($updated_handover, $updated_equipment);
                                if (!$email_sent) {
                                    // Log error but don't fail the handover process
                                    error_log("Failed to send email notification for equipment: $equipment_id");
                                }
                            }
                            
                            setSessionMessage('success', 'Equipment handover completed successfully.');
                            header('Location: PickupForm.php?id=' . $equipment_id);
                            exit();
                        } catch (Exception $e) {
                            // Rollback transaction
                            $conn->rollback();
                            $conn->autocommit(TRUE);
                            $error = 'Database error: ' . $e->getMessage();
                        }
                    }
                } else {
                    $error = 'Equipment not found.';
                }
                
                $check_stmt->close();
            } else {
                // Old structure: Use handover table (backward compatibility)
                $table_check = $conn->query("SHOW TABLES LIKE 'handover'");
                if ($table_check && $table_check->num_rows > 0) {
                    // Old handover table logic here (for backward compatibility)
                    $error = 'Please update to new equipment table structure.';
                } else {
                    $error = 'Equipment table does not exist.';
                }
            }
            
            $conn->close();
        } catch (Exception $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}

// Get equipment ID from URL (changed from handover_id)
$equipment_id = isset($_GET['id']) ? trim($_GET['id']) : '';

if (empty($equipment_id)) {
    $error = 'Equipment ID is required.';
} else {
    try {
        $conn = getDBConnection();
        
        // Check if equipment table exists
        $equipment_table_check = $conn->query("SHOW TABLES LIKE 'equipment'");
        $has_equipment_table = $equipment_table_check && $equipment_table_check->num_rows > 0;
        
        if ($has_equipment_table) {
            // New structure: Get handover data from equipment table
            $stmt = $conn->prepare("SELECT e.*, c.category_name as category FROM equipment e LEFT JOIN categories c ON e.category_id = c.id WHERE e.equipment_id = ?");
            $stmt->bind_param("s", $equipment_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 1) {
                $equipment = $result->fetch_assoc();
                
                // Convert equipment data to handover format for display (backward compatibility)
                $handover = [
                    'equipment_id' => $equipment['equipment_id'],
                    'equipment_name' => $equipment['equipment_name'],
                    'lecturer_id' => $equipment['staff_id'],
                    'lecturer_name' => $equipment['staff_name'],
                    'lecturer_email' => $equipment['staff_email'],
                    'pickup_date' => $equipment['pickup_date'],
                    'return_date' => $equipment['return_date'],
                    'handoverStat' => $equipment['handover_status'],
                    'handoverStaff' => $equipment['handover_staff'],
                    'returnStaff' => $equipment['return_staff']
                ];
                
                // Equipment data is already fetched above
                
                // Fetch staff names from technician table
                if (!empty($handover['handoverStaff'])) {
                    $staff_stmt = $conn->prepare("SELECT full_name FROM technician WHERE staff_id = ?");
                    $staff_stmt->bind_param("s", $handover['handoverStaff']);
                    $staff_stmt->execute();
                    $staff_result = $staff_stmt->get_result();
                    
                    if ($staff_result->num_rows === 1) {
                        $staff_row = $staff_result->fetch_assoc();
                        $handoverStaffName = $staff_row['full_name'];
                    }
                    
                    $staff_stmt->close();
                }
                
                if (!empty($handover['returnStaff'])) {
                    $staff_stmt = $conn->prepare("SELECT full_name FROM technician WHERE staff_id = ?");
                    $staff_stmt->bind_param("s", $handover['returnStaff']);
                    $staff_stmt->execute();
                    $staff_result = $staff_stmt->get_result();
                    
                    if ($staff_result->num_rows === 1) {
                        $staff_row = $staff_result->fetch_assoc();
                        $returnStaffName = $staff_row['full_name'];
                    }
                    
                    $staff_stmt->close();
                }
            } else {
                $error = 'Equipment not found.';
            }
            
            $stmt->close();
        } else {
            // Old structure: Use handover table (backward compatibility)
            $table_check = $conn->query("SHOW TABLES LIKE 'handover'");
            if ($table_check && $table_check->num_rows > 0) {
                // Old handover table logic here (for backward compatibility)
                $error = 'Please update to new equipment table structure.';
            } else {
                $error = 'Equipment table does not exist.';
            }
        }
        
        $conn->close();
    } catch (Exception $e) {
        $error = 'Database error: ' . $e->getMessage();
    }
}

// Get session messages
$message = getSessionMessage();
if ($message) {
    if ($message['type'] === 'success') {
        $success = $message['content'];
    } else {
        $error = $message['content'];
    }
}

// Determine page title based on handover status
$pageTitle = 'Handover Details';
if ($handover && $handover['handoverStat'] !== 'pending') {
    $pageTitle = 'View Handover Details';
}

// Set active page and title for header component
$activePage = 'pickup';
$additionalCSS = ['../css/PickupForm.css'];
$additionalJS = ['../js/PickupForm.js'];

// Include header component
require_once __DIR__ . '/../component/header.php';
?>

<div class="container">
    <div class="page-header">
        <div class="header-content">
            <div>
                <a href="Pickup.php" class="back-link">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="15 18 9 12 15 6"></polyline>
                    </svg>
                    <span>Back to Pickup</span>
                </a>
                <h1><?php echo htmlspecialchars($pageTitle); ?></h1>
                <?php if ($handover): ?>
                    <?php
                    $status_map = [
                        'pending' => 'Pending',
                        'picked_up' => 'Picked Up',
                        'returned' => 'Returned'
                    ];
                    $display_status = $status_map[$handover['handoverStat']] ?? ucfirst($handover['handoverStat']);
                    ?>
                    <p class="page-subtitle">
                        <?php if ($handover['handoverStat'] === 'pending'): ?>
                            Review and confirm handover details
                        <?php else: ?>
                            View handover details - Status: <?php echo htmlspecialchars($display_status); ?>
                        <?php endif; ?>
                    </p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php if ($error): ?>
        <div class="error-message">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="success-message">
            <?php echo htmlspecialchars($success); ?>
        </div>
    <?php endif; ?>
    <?php if ($handover && $equipment): ?>
        <?php
        // Determine if handover is pending (show agreement) or already processed (show details only)
        $is_pending = ($handover['handoverStat'] === 'pending');
        ?>
        <div class="handover-content">
            <!-- Equipment Details Section -->
            <div class="details-section">
                <div class="details-card equipment-card">
                    <div class="card-header">
                        <h2>Equipment Information</h2>
                    </div>
                    <div class="card-body">
                        <div class="details-grid">
                            <div class="detail-item">
                                <div class="detail-label">Equipment ID</div>
                                <div class="detail-value"><?php echo htmlspecialchars($equipment['equipment_id'] ?? 'N/A'); ?></div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Equipment Name</div>
                                <div class="detail-value"><?php echo htmlspecialchars($equipment['equipment_name'] ?? 'N/A'); ?></div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Category</div>
                                <div class="detail-value">
                                    <?php
                                    $category_name = $equipment['category'] ?? 'N/A';
                                    $category_class = strtolower(str_replace([' ', '&', '/'], ['-', '', '-'], $category_name));
                                    ?>
                                    <span class="category-badge category-<?php echo htmlspecialchars($category_class); ?>">
                                        <?php echo htmlspecialchars($category_name); ?>
                                    </span>
                                </div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Brand</div>
                                <div class="detail-value"><?php echo htmlspecialchars($equipment['brand'] ?? 'N/A'); ?></div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Model</div>
                                <div class="detail-value"><?php echo htmlspecialchars($equipment['model'] ?? 'N/A'); ?></div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Serial Number</div>
                                <div class="detail-value serial-number"><?php echo htmlspecialchars($equipment['serial_number'] ?? 'N/A'); ?></div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Location</div>
                                <div class="detail-value"><?php echo htmlspecialchars($equipment['location'] ?? 'N/A'); ?></div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Status</div>
                                <div class="detail-value">
                                    <?php
                                    $status = $equipment['status'] ?? 'N/A';
                                    $status_class = strtolower(str_replace(' ', '-', $status));
                                    ?>
                                    <span class="status-badge status-<?php echo $status_class; ?>">
                                        <?php echo htmlspecialchars($status); ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <?php if (!empty($equipment['description'])): ?>
                            <div class="description-section">
                                <div class="detail-label">Description</div>
                                <div class="detail-value description-text"><?php echo nl2br(htmlspecialchars($equipment['description'])); ?></div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Handover Details Section -->
                <div class="details-card handover-card">
                    <div class="card-header">
                        <h2>Handover Details</h2>
                    </div>
                    <div class="card-body">
                        <div class="details-grid">
                            <div class="detail-item">
                                <div class="detail-label">Equipment ID</div>
                                <div class="detail-value equipment-id"><?php echo htmlspecialchars($handover['equipment_id']); ?></div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Lecturer ID</div>
                                <div class="detail-value"><?php echo htmlspecialchars($handover['lecturer_id'] ?? 'N/A'); ?></div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Lecturer Name</div>
                                <div class="detail-value"><?php echo htmlspecialchars($handover['lecturer_name'] ?? 'N/A'); ?></div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Email</div>
                                <div class="detail-value"><?php echo htmlspecialchars($handover['lecturer_email'] ?? 'N/A'); ?></div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Pickup Date</div>
                                <div class="detail-value"><?php echo $handover['pickup_date'] ? date('d M Y', strtotime($handover['pickup_date'])) : 'N/A'; ?></div>
                            </div>
                            <?php if (!empty($handover['return_date'])): ?>
                                <div class="detail-item">
                                    <div class="detail-label">Return Date</div>
                                    <div class="detail-value"><?php echo date('d M Y', strtotime($handover['return_date'])); ?></div>
                                </div>
                            <?php endif; ?>
                            <div class="detail-item">
                                <div class="detail-label">Status</div>
                                <div class="detail-value">
                                    <?php
                                    $status_map = [
                                        'pending' => 'Pending',
                                        'picked_up' => 'Picked Up',
                                        'returned' => 'Returned'
                                    ];
                                    $display_status = $status_map[$handover['handoverStat']] ?? ucfirst($handover['handoverStat']);
                                    $status_class = strtolower(str_replace(' ', '-', $display_status));
                                    ?>
                                    <span class="status-badge status-<?php echo $status_class; ?>">
                                        <?php echo htmlspecialchars($display_status); ?>
                                    </span>
                                </div>
                            </div>
                            <?php if (!empty($handover['handoverStaff'])): ?>
                                <div class="detail-item">
                                    <div class="detail-label">Handover Staff</div>
                                    <div class="detail-value">
                                        <?php 
                                        if (!empty($handoverStaffName)) {
                                            echo htmlspecialchars($handoverStaffName) . ' (' . htmlspecialchars($handover['handoverStaff']) . ')';
                                        } else {
                                            echo htmlspecialchars($handover['handoverStaff']);
                                        }
                                        ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($handover['returnStaff'])): ?>
                                <div class="detail-item">
                                    <div class="detail-label">Return Staff</div>
                                    <div class="detail-value">
                                        <?php 
                                        if (!empty($returnStaffName)) {
                                            echo htmlspecialchars($returnStaffName) . ' (' . htmlspecialchars($handover['returnStaff']) . ')';
                                        } else {
                                            echo htmlspecialchars($handover['returnStaff']);
                                        }
                                        ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- User Agreement Section - Only show for pending handovers -->
            <?php if ($is_pending): ?>
                <div class="agreement-section">
                    <div class="agreement-card">
                        <div class="card-header">
                            <h2>User Agreement</h2>
                        </div>
                        <div class="card-body">
                            <div class="agreement-content">
                                <p class="agreement-intro">By proceeding with the handover, the lecturer agrees to the following terms and conditions:</p>
                                
                                <div class="agreement-terms">
                                    <div class="term-item">
                                        <div class="term-icon">
                                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                                <polyline points="22 4 12 14.01 9 11.01"></polyline>
                                            </svg>
                                        </div>
                                        <div class="term-text">
                                            <strong>Responsible Care:</strong> The lecturer is responsible for taking proper care of the equipment and ensuring it is used in accordance with its intended purpose.
                                        </div>
                                    </div>

                                    <div class="term-item">
                                        <div class="term-icon">
                                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                                <polyline points="22 4 12 14.01 9 11.01"></polyline>
                                            </svg>
                                        </div>
                                        <div class="term-text">
                                            <strong>Security:</strong> The lecturer must ensure the equipment is kept secure and protected from theft, damage, or unauthorized use.
                                        </div>
                                    </div>

                                    <div class="term-item">
                                        <div class="term-icon">
                                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                                <polyline points="22 4 12 14.01 9 11.01"></polyline>
                                            </svg>
                                        </div>
                                        <div class="term-text">
                                            <strong>Maintenance:</strong> The lecturer must report any issues, damages, or malfunctions immediately to the IT department.
                                        </div>
                                    </div>

                                    <div class="term-item">
                                        <div class="term-icon">
                                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                                <polyline points="22 4 12 14.01 9 11.01"></polyline>
                                            </svg>
                                        </div>
                                        <div class="term-text">
                                            <strong>Return Policy:</strong> The equipment must be returned in the same condition as received, with all accessories and documentation included.
                                        </div>
                                    </div>

                                    <div class="term-item">
                                        <div class="term-icon">
                                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                                <polyline points="22 4 12 14.01 9 11.01"></polyline>
                                            </svg>
                                        </div>
                                        <div class="term-text">
                                            <strong>Liability:</strong> The lecturer will be held financially responsible for any loss, damage, or theft of the equipment while in their possession.
                                        </div>
                                    </div>
                                </div>

                                <div class="agreement-checkbox">
                                    <label class="checkbox-label">
                                        <input type="checkbox" id="agreeTerms" name="agreeTerms" required>
                                        <span class="checkbox-custom"></span>
                                        <span class="checkbox-text">I have read and agree to the terms and conditions stated above</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Action Button Section - Only show for pending handovers -->
            <?php if ($is_pending): ?>
                <form method="POST" action="" id="handoverForm" class="action-section">
                    <input type="hidden" name="action" value="handover">
                    <input type="hidden" name="equipment_id" value="<?php echo htmlspecialchars($handover['equipment_id']); ?>">
                    <input type="hidden" name="agreeTerms" id="agreeTermsHidden" value="">
                    <button type="submit" class="btn-handover" id="handoverBtn" disabled>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                            <polyline points="22 4 12 14.01 9 11.01"></polyline>
                        </svg>
                        <span>Confirm Handover</span>
                    </button>
                </form>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../component/footer.php'; ?>
</body>
</html>

