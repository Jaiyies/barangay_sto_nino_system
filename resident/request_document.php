<?php
// resident/request_document.php
require_once '../config/database.php';
require_once '../config/session.php';
requireLogin();

$error = '';
$success = '';

$document_types = [
    'barangay_clearance' => 'Barangay Clearance',
    'certificate_indigency' => 'Certificate of Indigency',
    'proof_residency' => 'Proof of Residency'
];

// Civil status options
$civil_status = [
    'single' => 'Single',
    'married' => 'Married',
    'widowed' => 'Widowed',
    'separated' => 'Separated',
    'divorced' => 'Divorced'
];

// Relation options
$relations = [
    'owner' => 'Owner',
    'renter' => 'Renter',
    'sharer' => 'Sharer'
];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $db = new Database();
    $conn = $db->getConnection();
    
    $user_id = $_SESSION['user_id'];
    $document_type = $_POST['document_type'];
    $purpose = trim($_POST['purpose']);
    $length_of_stay = trim($_POST['length_of_stay']);
    
    // ===== VALIDATION: PROOF OF RESIDENCY =====
    if ($document_type == 'proof_residency') {
        preg_match('/(\d+)/', $length_of_stay, $matches);
        $years = isset($matches[1]) ? intval($matches[1]) : 0;
        
        if ($years < 1) {
            $error = "❌ For Proof of Residency, you must have lived in the barangay for at least 1 year.";
        }
    }
    
    // Barangay Form Fields
    $surname = trim($_POST['surname']);
    $given_name = trim($_POST['given_name']);
    $middle_name = trim($_POST['middle_name']);
    $address = trim($_POST['address']);
    $civil_status = trim($_POST['civil_status']);
    $age = intval($_POST['age']);
    $birthdate = trim($_POST['birthdate']);
    $birthplace = trim($_POST['birthplace']);
    $contact = trim($_POST['contact']);
    $precinct = trim($_POST['precinct']);
    $relation = trim($_POST['relation']);
    $lessor_name = trim($_POST['lessor_name']);
    $lessor_address = trim($_POST['lessor_address']);
    $father_name = trim($_POST['father_name']);
    $mother_name = trim($_POST['mother_name']);
    $spouse_name = trim($_POST['spouse_name']);
    $occupation = trim($_POST['occupation']);
    $emergency_name = trim($_POST['emergency_name']);
    $emergency_contact = trim($_POST['emergency_contact']);
    $ref_name1 = trim($_POST['ref_name1']);
    $ref_address1 = trim($_POST['ref_address1']);
    $ref_name2 = trim($_POST['ref_name2']);
    $ref_address2 = trim($_POST['ref_address2']);
    
    // Signature image upload
    $signature_path = '';
    if (isset($_FILES['signature_image']) && $_FILES['signature_image']['error'] == 0) {
        $upload_dir = '../uploads/signatures/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $file_ext = strtolower(pathinfo($_FILES['signature_image']['name'], PATHINFO_EXTENSION));
        
        if (in_array($file_ext, $allowed)) {
            $signature_path = $upload_dir . time() . '_' . uniqid() . '.' . $file_ext;
            move_uploaded_file($_FILES['signature_image']['tmp_name'], $signature_path);
        }
    }
    
    // Proceed only if no error
    if (empty($error)) {
        $tracking_code = 'DOC-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
        
        $query = "INSERT INTO document_requests (
            user_id, document_type, purpose, tracking_code, status,
            surname, given_name, middle_name, address, civil_status, age, birthdate, birthplace,
            contact, precinct, length_of_stay, relation, lessor_name, lessor_address,
            father_name, mother_name, spouse_name, occupation,
            emergency_name, emergency_contact,
            ref_name1, ref_address1, ref_name2, ref_address2,
            signature_image
        ) VALUES (
            ?, ?, ?, ?, 'pending',
            ?, ?, ?, ?, ?, ?, ?, ?,
            ?, ?, ?, ?, ?, ?,
            ?, ?, ?, ?,
            ?, ?,
            ?, ?, ?, ?,
            ?
        )";
        
        $stmt = $conn->prepare($query);
        
        if ($stmt->execute([
            $user_id, $document_type, $purpose, $tracking_code,
            $surname, $given_name, $middle_name, $address, $civil_status, $age, $birthdate, $birthplace,
            $contact, $precinct, $length_of_stay, $relation, $lessor_name, $lessor_address,
            $father_name, $mother_name, $spouse_name, $occupation,
            $emergency_name, $emergency_contact,
            $ref_name1, $ref_address1, $ref_name2, $ref_address2,
            $signature_path
        ])) {
            $success = "✅ Document request submitted! Tracking Code: <strong>$tracking_code</strong>";
        } else {
            $error = "❌ Failed to submit request. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Document</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #f0f5f2; font-family: 'Segoe UI', sans-serif; }
        .form-card {
            border-radius: 24px;
            border: none;
            box-shadow: 0 10px 40px rgba(0,0,0,0.08);
        }
        .form-card .card-header {
            border-radius: 24px 24px 0 0;
            background: linear-gradient(135deg, #0d3b26, #1a5d3a);
            color: white;
            padding: 20px 30px;
        }
        .form-card .card-header small {
            opacity: 0.8;
        }
        
        /* ===== GREEN NAVBAR ===== */
        .navbar-custom {
            background: linear-gradient(135deg, #0d3b26, #1a5d3a) !important;
            padding: 12px 0;
            box-shadow: 0 2px 15px rgba(13, 59, 38, 0.2);
        }
        .navbar-custom .navbar-brand {
            color: white !important;
            font-weight: 600;
        }
        .navbar-custom .navbar-brand:hover {
            color: #c9e0d6 !important;
        }
        .navbar-custom .navbar-brand i {
            margin-right: 8px;
        }
        .navbar-custom .text-white {
            color: rgba(255,255,255,0.9) !important;
        }
        .navbar-custom .btn-light {
            background: rgba(255,255,255,0.15);
            border: 1px solid rgba(255,255,255,0.2);
            color: white;
        }
        .navbar-custom .btn-light:hover {
            background: rgba(255,255,255,0.25);
            color: white;
        }
        
        .form-control, .form-select {
            border-radius: 12px;
            padding: 12px 16px;
            border: 1.5px solid #e2e9ef;
        }
        .form-control:focus, .form-select:focus {
            border-color: #166534;
            box-shadow: 0 0 0 4px rgba(22, 101, 52, 0.08);
        }
        .btn-submit {
            background: linear-gradient(135deg, #0d3b26, #1a5d3a);
            color: white;
            border: none;
            padding: 14px;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
            width: 100%;
        }
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(13, 59, 38, 0.3);
            color: white;
        }
        .section-title {
            font-weight: 700;
            color: #0d3b26;
            border-bottom: 2px solid #166534;
            padding-bottom: 8px;
            margin-bottom: 20px;
        }
        .header-barangay {
            background: white;
            padding: 10px 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            text-align: center;
            border: 1px solid #e2e9ef;
        }
        .header-barangay small {
            display: block;
            color: #6b7c6f;
        }
        .header-barangay strong {
            color: #0d3b26;
        }
        .form-label {
            font-weight: 600;
            font-size: 0.85rem;
            color: #2c4b3e;
        }
        .form-label .optional {
            font-weight: 400;
            color: #6b7c6f;
            font-size: 0.75rem;
        }
        .row.gap-3 > * {
            margin-bottom: 10px;
        }
        .alert {
            border-radius: 12px;
        }
        
        /* ===== CONDITIONAL FIELD ===== */
        .conditional-field {
            display: none;
            animation: fadeIn 0.3s ease;
        }
        .conditional-field.show {
            display: block;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        /* ===== FILE UPLOAD ===== */
        .signature-upload-wrapper {
            border: 2px dashed #e2e9ef;
            border-radius: 12px;
            padding: 30px;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
            background: #fafdfb;
        }
        .signature-upload-wrapper:hover {
            border-color: #166534;
            background: #f0f7f2;
        }
        .signature-upload-wrapper i {
            font-size: 2.5rem;
            color: #a0b8ae;
            margin-bottom: 10px;
        }
        .signature-upload-wrapper p {
            color: #6b7c6f;
            font-size: 0.9rem;
            margin-bottom: 4px;
        }
        .signature-upload-wrapper small {
            color: #a0b8ae;
            font-size: 0.8rem;
        }
        .signature-upload-wrapper input[type="file"] {
            display: none;
        }
        .signature-preview {
            margin-top: 10px;
            display: none;
        }
        .signature-preview img {
            max-width: 200px;
            max-height: 100px;
            border: 1px solid #e2e9ef;
            border-radius: 8px;
            padding: 5px;
            background: white;
        }
        .signature-preview .remove-signature {
            color: #dc3545;
            cursor: pointer;
            font-size: 0.85rem;
            margin-left: 10px;
        }
        .signature-preview .remove-signature:hover {
            text-decoration: underline;
        }
        
        .validation-hint {
            font-size: 0.8rem;
            color: #6b7c6f;
            margin-top: 4px;
        }
        .validation-hint .required-text {
            color: #dc3545;
        }
    </style>
</head>
<body>
    <!-- ===== GREEN NAVBAR ===== -->
    <nav class="navbar navbar-expand-lg navbar-custom">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
            <div class="ms-auto d-flex align-items-center gap-3">
                <span class="text-white"><i class="fas fa-user"></i> <?= htmlspecialchars($_SESSION['full_name']) ?></span>
                <a href="../auth/logout.php" class="btn btn-light btn-sm">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card form-card">
                    <div class="card-header">
                        <h4 class="mb-0"><i class="fas fa-file-alt me-2"></i> Barangay Clearance / Indigency Form</h4>
                        <small>Republic of the Philippines | City of Parañaque | Barangay Sto. Niño</small>
                    </div>
                    <div class="card-body p-4">
                        <?php if($error): ?>
                            <div class="alert alert-danger"><?= $error ?></div>
                        <?php endif; ?>
                        <?php if($success): ?>
                            <div class="alert alert-success"><?= $success ?></div>
                        <?php endif; ?>

                        <div class="header-barangay">
                            <small>Republica ng Pilipinas</small>
                            <strong>LUNGSOD NG PARANAQUE</strong>
                            <strong>BARANGAY NG STO. NIÑO</strong>
                            <small>E. Rodriguez Avenue, Sto. Niño Parañaque City</small>
                            <small>Tel. No.: 8852-7477</small>
                        </div>

                        <form method="POST" enctype="multipart/form-data">
                            <!-- ===== DOCUMENT TYPE ===== -->
                            <div class="mb-4">
                                <label class="form-label">Document Type <span class="text-danger">*</span></label>
                                <select name="document_type" id="documentType" class="form-select" required>
                                    <option value="">Select Document Type</option>
                                    <?php foreach($document_types as $key => $value): ?>
                                        <option value="<?= $key ?>"><?= $value ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- ===== PERSONAL INFORMATION ===== -->
                            <h6 class="section-title"><i class="fas fa-user me-2"></i> Personal Information</h6>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Surname <span class="text-danger">*</span></label>
                                    <input type="text" name="surname" class="form-control" placeholder="Surname" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Given Name <span class="text-danger">*</span></label>
                                    <input type="text" name="given_name" class="form-control" placeholder="Given Name" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Middle Name</label>
                                    <input type="text" name="middle_name" class="form-control" placeholder="Middle Name">
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label">Address <span class="text-danger">*</span></label>
                                    <input type="text" name="address" class="form-control" placeholder="Complete Address" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Civil Status</label>
                                    <select name="civil_status" class="form-select">
                                        <?php foreach($civil_status as $key => $value): ?>
                                            <option value="<?= $key ?>"><?= $value ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Age</label>
                                    <input type="number" name="age" class="form-control" placeholder="Age">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Date of Birth</label>
                                    <input type="date" name="birthdate" class="form-control">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Place of Birth</label>
                                    <input type="text" name="birthplace" class="form-control" placeholder="Place of Birth">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Contact No.</label>
                                    <input type="text" name="contact" class="form-control" placeholder="0912 345 6789">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Precinct No.</label>
                                    <input type="text" name="precinct" class="form-control" placeholder="Precinct Number">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">
                                        Length of Stay in Barangay 
                                        <span class="text-danger" id="lengthOfStayRequired">*</span>
                                    </label>
                                    <input type="text" name="length_of_stay" id="lengthOfStay" class="form-control" placeholder="e.g., 5 years" required>
                                    <div class="validation-hint" id="lengthOfStayHint">
                                        <i class="fas fa-info-circle"></i> 
                                        <span class="required-text" id="lengthOfStayText">For Proof of Residency, at least 1 year is required.</span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Relation</label>
                                    <select name="relation" class="form-select">
                                        <?php foreach($relations as $key => $value): ?>
                                            <option value="<?= $key ?>"><?= $value ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Lessor (Property Owner of Your Residence)</label>
                                    <input type="text" name="lessor_name" class="form-control" placeholder="Full name of property owner/lessor">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Lessor Address</label>
                                    <input type="text" name="lessor_address" class="form-control" placeholder="Address of the property owner/lessor">
                                </div>
                            </div>

                            <!-- ===== FAMILY INFORMATION ===== -->
                            <h6 class="section-title mt-4"><i class="fas fa-users me-2"></i> Family Information</h6>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Father's Name</label>
                                    <input type="text" name="father_name" class="form-control" placeholder="Father's Name">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Mother's Name</label>
                                    <input type="text" name="mother_name" class="form-control" placeholder="Mother's Name">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Spouse's Name <span class="optional">(Optional)</span></label>
                                    <input type="text" name="spouse_name" class="form-control" placeholder="Spouse's Name (if any)">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Occupation</label>
                                    <input type="text" name="occupation" class="form-control" placeholder="Occupation">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Emergency Contact Name</label>
                                    <input type="text" name="emergency_name" class="form-control" placeholder="Emergency Contact Name">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Emergency Contact No.</label>
                                    <input type="text" name="emergency_contact" class="form-control" placeholder="Emergency Contact Number">
                                </div>
                            </div>

                            <!-- ===== REFERENCES ===== -->
                            <h6 class="section-title mt-4"><i class="fas fa-address-book me-2"></i> Reference Person (Not Related to You)</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Name 1</label>
                                    <input type="text" name="ref_name1" class="form-control" placeholder="Reference Name 1">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Address 1</label>
                                    <input type="text" name="ref_address1" class="form-control" placeholder="Reference Address 1">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Name 2</label>
                                    <input type="text" name="ref_name2" class="form-control" placeholder="Reference Name 2">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Address 2</label>
                                    <input type="text" name="ref_address2" class="form-control" placeholder="Reference Address 2">
                                </div>
                            </div>

                            <!-- ===== CONDITIONAL FIELD: Signature of Area Representative ===== -->
                            <div class="conditional-field" id="areaRepField">
                                <h6 class="section-title mt-4"><i class="fas fa-pen me-2"></i> Proof of Residency Requirement</h6>
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="form-label">Signature of Area Representative <span class="text-danger">*</span></label>
                                        <div class="signature-upload-wrapper" id="signatureDropArea">
                                            <i class="fas fa-cloud-upload-alt"></i>
                                            <p>Click to upload e-signature image</p>
                                            <small>JPG, PNG, GIF (Max 2MB)</small>
                                            <input type="file" name="signature_image" id="signatureInput" accept=".jpg,.jpeg,.png,.gif">
                                        </div>
                                        <div class="signature-preview" id="signaturePreview">
                                            <img id="signaturePreviewImg" src="#" alt="Signature Preview">
                                            <span class="remove-signature" id="removeSignature"><i class="fas fa-times"></i> Remove</span>
                                        </div>
                                        <small class="text-muted">Upload a clear image of the area representative's signature</small>
                                    </div>
                                </div>
                            </div>

                            <!-- ===== PURPOSE ===== -->
                            <h6 class="section-title mt-4"><i class="fas fa-bullseye me-2"></i> Purpose</h6>
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label">Purpose of Securing Barangay Clearance <span class="text-danger">*</span></label>
                                    <textarea name="purpose" class="form-control" rows="3" placeholder="State the purpose of your request..." required></textarea>
                                </div>
                            </div>

                            <button type="submit" class="btn-submit mt-4">
                                <i class="fas fa-paper-plane me-2"></i> Submit Request
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // ===== CONDITIONAL FIELD LOGIC =====
        document.addEventListener('DOMContentLoaded', function() {
            const documentType = document.getElementById('documentType');
            const areaRepField = document.getElementById('areaRepField');
            const signatureInput = document.getElementById('signatureInput');
            const lengthOfStay = document.getElementById('lengthOfStay');
            const lengthOfStayRequired = document.getElementById('lengthOfStayRequired');
            const lengthOfStayHint = document.getElementById('lengthOfStayHint');
            const lengthOfStayText = document.getElementById('lengthOfStayText');
            
            function toggleAreaRepField() {
                if (documentType.value === 'proof_residency') {
                    areaRepField.classList.add('show');
                    signatureInput.setAttribute('required', 'required');
                    lengthOfStayRequired.style.display = 'inline';
                    lengthOfStayHint.style.display = 'block';
                } else {
                    areaRepField.classList.remove('show');
                    signatureInput.removeAttribute('required');
                    lengthOfStayRequired.style.display = 'none';
                    lengthOfStayHint.style.display = 'none';
                }
            }
            
            toggleAreaRepField();
            documentType.addEventListener('change', toggleAreaRepField);
        });

        // ===== SIGNATURE IMAGE UPLOAD =====
        const signatureInput = document.getElementById('signatureInput');
        const signatureDropArea = document.getElementById('signatureDropArea');
        const signaturePreview = document.getElementById('signaturePreview');
        const signaturePreviewImg = document.getElementById('signaturePreviewImg');
        const removeSignature = document.getElementById('removeSignature');

        signatureDropArea.addEventListener('click', () => signatureInput.click());

        signatureDropArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            signatureDropArea.style.borderColor = '#166534';
            signatureDropArea.style.background = '#f0f7f2';
        });

        signatureDropArea.addEventListener('dragleave', () => {
            signatureDropArea.style.borderColor = '#e2e9ef';
            signatureDropArea.style.background = '#fafdfb';
        });

        signatureDropArea.addEventListener('drop', (e) => {
            e.preventDefault();
            signatureDropArea.style.borderColor = '#e2e9ef';
            signatureDropArea.style.background = '#fafdfb';
            if (e.dataTransfer.files.length) {
                signatureInput.files = e.dataTransfer.files;
                previewSignature(e.dataTransfer.files[0]);
            }
        });

        signatureInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                previewSignature(this.files[0]);
            }
        });

        function previewSignature(file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                signaturePreviewImg.src = e.target.result;
                signaturePreview.style.display = 'block';
                signatureDropArea.style.display = 'none';
            };
            reader.readAsDataURL(file);
        }

        removeSignature.addEventListener('click', function() {
            signatureInput.value = '';
            signaturePreview.style.display = 'none';
            signatureDropArea.style.display = 'block';
            signaturePreviewImg.src = '#';
        });
    </script>

</body>
</html>