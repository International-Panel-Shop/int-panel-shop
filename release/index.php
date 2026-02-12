<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IPS Vehicle Release</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .signature-pad { border: 2px dashed #ccc; background: #fff; width: 100%; height: 200px; touch-action: none; }
        .card { box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
        .table-input { border: none; background: transparent; width: 100%; }
        .table-input:focus { outline: none; background: #f0f0f0; }
    </style>
</head>
<body>

<div class="container py-4">
    <div class="card p-4">
        <div class="text-center mb-4">
            <img src="assets/logo.png" alt="IPS Logo" style="max-height: 80px;">
            <h3 class="mt-2">Vehicle Release Checklist</h3>
        </div>

        <form id="releaseForm" action="process.php" method="POST">
            <input type="hidden" name="customer_sig_data" id="customer_sig_data">
            
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Customer Name</label>
                    <input type="text" name="customer_name" class="form-control" required>
                </div>
                 <div class="col-md-6">
                    <label class="form-label">Customer Email</label>
                    <input type="email" name="customer_email" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Vehicle Make & Model</label>
                    <input type="text" name="vehicle_make" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Registration Number</label>
                    <input type="text" name="reg_number" class="form-control" required>
                </div>
                 <div class="col-md-6">
                    <label class="form-label">Job Card / Ref No.</label>
                    <input type="text" name="job_card" class="form-control">
                </div>
            </div>

            <hr class="my-4">

            <h5>Release Checklist</h5>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 50%;">Item</th>
                            <th style="width: 15%; text-align: center;">Checked</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $items = [
                            "Panelbeating Repairs Completed",
                            "Paint Work Quality & Finish",
                            "Colour Match Verified",
                            "Vehicle Cleaned (Exterior)",
                            "All Personal Belongings Returned",
                            "Customer Queries Addressed"
                        ];
                        foreach($items as $index => $item): ?>
                        <tr>
                            <td>
                                <?php echo $item; ?>
                                <input type="hidden" name="items[<?php echo $index; ?>]" value="<?php echo $item; ?>">
                            </td>
                            <td class="text-center">
                                <input class="form-check-input" type="checkbox" name="checked[<?php echo $index; ?>]" value="Yes" style="transform: scale(1.5);">
                            </td>
                            <td>
                                <input type="text" class="table-input" name="notes[<?php echo $index; ?>]" placeholder="Add note...">
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="alert alert-secondary mt-3">
                <strong>Customer Confirmation:</strong>
                <p class="mb-2">I hereby confirm that I have inspected my vehicle and I am satisfied with the panelbeating and spraypainting repairs completed by International Panel Shop. I understand that any defects or issues found after the release of the vehicle which are unrelated to the completed repairs will not be the responsibility of the workshop.</p>
                
                <strong>Guarantee:</strong>
                <ul class="mb-0">
                    <li>12 months guarantee applies to all paint work.</li>
                    <li>No guarantee is provided for rust repairs.</li>
                </ul>
            </div>

            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" id="termsCheck" required>
                <label class="form-check-label" for="termsCheck">
                    I confirm the above details are correct.
                </label>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Customer Signature</label>
                <canvas id="sigCanvas" class="signature-pad rounded"></canvas>
                <button type="button" class="btn btn-sm btn-outline-secondary mt-1" onclick="clearSig()">Clear Signature</button>
            </div>

            <button type="submit" class="btn btn-success w-100 py-3 fw-bold">Sign Release & Send</button>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
<script>
    var canvas = document.getElementById('sigCanvas');
    var signaturePad = new SignaturePad(canvas);

    function resizeCanvas() {
        var ratio =  Math.max(window.devicePixelRatio || 1, 1);
        canvas.width = canvas.offsetWidth * ratio;
        canvas.height = canvas.offsetHeight * ratio;
        canvas.getContext("2d").scale(ratio, ratio);
        signaturePad.clear();
    }
    window.addEventListener("resize", resizeCanvas);
    resizeCanvas();

    function clearSig() { signaturePad.clear(); }

    document.getElementById('releaseForm').addEventListener('submit', function(e) {
        if (signaturePad.isEmpty()) {
            e.preventDefault();
            alert("Please provide a signature.");
        } else {
            document.getElementById('customer_sig_data').value = signaturePad.toDataURL();
        }
    });
</script>
</body>
</html>