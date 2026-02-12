<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IPS Vehicle Check-In</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .signature-pad { border: 2px dashed #ccc; background: #fff; width: 100%; height: 200px; touch-action: none; }
        .card { box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
    </style>
</head>
<body>

<div class="container py-4">
    <div class="card p-4">
        <div class="text-center mb-4">
            <img src="assets/logo.png" alt="IPS Logo" style="max-height: 80px;">
            <h3 class="mt-2">Vehicle Check-In</h3>
        </div>

        <form id="checkinForm" action="process.php" method="POST">
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

            <h5>Terms & Conditions</h5>
            <div class="alert alert-secondary" style="font-size: 0.9em; max-height: 200px; overflow-y: auto;">
                <p>By signing this form, the customer acknowledges and agrees to:</p>
                <ol>
                    <li>The vehicle is here for panelbeating and/or spraypainting repairs.</li>
                    <li>IPS is not liable for loss of personal belongings left in the vehicle.</li>
                    <li>Authorization is granted for inspection, repairs, and necessary testing.</li>
                    <li>Additional work will be quoted and requires approval.</li>
                    <li>Replaced parts may be disposed of unless requested otherwise.</li>
                    <li>Repair timelines may vary due to parts/paint curing.</li>
                </ol>
                <strong>Guarantee:</strong>
                <ul>
                    <li>12 months guarantee on paint work.</li>
                    <li>No guarantee on rust, glass, or electronics.</li>
                </ul>
            </div>

            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" id="termsCheck" required>
                <label class="form-check-label" for="termsCheck">
                    I accept the Terms and Guarantee Policy.
                </label>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Customer Signature</label>
                <canvas id="sigCanvas" class="signature-pad rounded"></canvas>
                <button type="button" class="btn btn-sm btn-outline-secondary mt-1" onclick="clearSig()">Clear Signature</button>
            </div>

            <button type="submit" class="btn btn-success w-100 py-3 fw-bold">Sign & Submit</button>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
<script>
    var canvas = document.getElementById('sigCanvas');
    var signaturePad = new SignaturePad(canvas);

    // Resize canvas for high DPI screens
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

    // On Submit, put the image data into the hidden input
    document.getElementById('checkinForm').addEventListener('submit', function(e) {
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