<!DOCTYPE html> 
<html lang="en"> 
<head> 
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <title>Kibandaski WiFi - Login</title> 
    <style> 
        :root { 
            --primary: #00843D; 
            --primary-dark: #006830; 
            --bg-color: #f4f7f6; 
            --card-bg: #ffffff; 
            --text-main: #333333; 
            --text-muted: #666666; 
            --border-color: #e0e0e0; 
        } 
 
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: -apple-system, 
BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; } 
        body { background-color: var(--bg-color); color: var(--text-main); display: flex; 
justify-content: center; align-items: center; min-height: 100vh; padding: 16px; } 
        .container { width: 100%; max-width: 400px; background: var(--card-bg); border-radius: 
12px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); padding: 24px; } 
        .header { text-align: center; margin-bottom: 24px; } 
        .header h1 { font-size: 1.4rem; margin-bottom: 6px; } 
        .header p { font-size: 0.9rem; color: var(--text-muted); } 
        .packages { display: flex; flex-direction: column; gap: 12px; margin-bottom: 24px; } 
        .package-card { border: 2px solid var(--border-color); border-radius: 8px; padding: 14px 
16px; display: flex; justify-content: space-between; align-items: center; cursor: pointer; transition: 
0.2s; } 
        .package-card:hover { border-color: var(--primary); background: rgba(0,132,61,0.02); } 
        .pkg-info h3 { font-size: 1rem; font-weight: 600; } 
        .pkg-info span { font-size: 0.8rem; color: var(--text-muted); } 
        .pkg-price { font-size: 1.1rem; font-weight: 700; color: var(--primary); } 
        .form-group { display: flex; flex-direction: column; gap: 6px; margin-bottom: 14px; } 
        .form-group label { font-size: 0.85rem; font-weight: 500; color: var(--text-muted); } 
        .form-group input { width: 100%; padding: 12px; border: 1px solid var(--border-color); 
border-radius: 6px; font-size: 1rem; outline: none; } 
        .form-group input:focus { border-color: var(--primary); } 
        .btn { background-color: var(--primary); color: white; border: none; border-radius: 6px; 
padding: 12px; font-size: 1rem; font-weight: 600; cursor: pointer; width: 100%; } 
        .btn:hover { background-color: var(--primary-dark); } 
        .back-link { text-align: center; margin-top: 12px; font-size: 0.85rem; color: var(--primary); 
cursor: pointer; text-decoration: underline; } 
        .spinner { width: 40px; height: 40px; border: 4px solid var(--border-color); border-top: 4px 
solid var(--primary); border-radius: 50%; animation: spin 1s linear infinite; margin: 20px auto; } 
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } } 
    </style> 
</head> 
<body> 
    <div class="container"> 
        <!-- Step 1: Packages --> 
        <div id="step-packages"> 
            <div class="header"> 
                <h1>Kibandaski WiFi</h1> 
                <p>Select a package to connect</p> 
            </div> 
            <div class="packages"> 
                <div class="package-card" onclick="selectPackage('15min', 10, '15 Minutes')"> 
                    <div class="pkg-info"><h3>15 Minutes</h3><span>Quick browse</span></div> 
                    <div class="pkg-price">KSh 10</div> 
                </div> 
                <div class="package-card" onclick="selectPackage('1hr', 20, '1 Hour')"> 
                    <div class="pkg-info"><h3>1 Hour</h3><span>Standard pass</span></div> 
                    <div class="pkg-price">KSh 20</div> 
                </div> 
                <div class="package-card" onclick="selectPackage('3hr', 50, '3 Hours')"> 
                    <div class="pkg-info"><h3>3 Hours</h3><span>Extended session</span></div> 
                    <div class="pkg-price">KSh 50</div> 
                </div> 
                <div class="package-card" onclick="selectPackage('daily', 100, '24 Hours')"> 
                    <div class="pkg-info"><h3>Daily Pass</h3><span>Full day access</span></div> 
                    <div class="pkg-price">KSh 100</div> 
                </div> 
            </div> 
        </div> 
 
        <!-- Step 2: Phone Input Form --> 
        <div id="step-form" style="display: none;"> 
            <div class="header"> 
                <h1>M-Pesa Payment</h1> 
                <p id="selected-summary"></p> 
            </div> 
            <form onsubmit="handlePayment(event)"> 
                <div class="form-group"> 
                    <label for="phone">M-Pesa Phone Number</label> 
                    <input type="tel" id="phone" placeholder="07XXXXXXXX" required> 
                </div> 
                <button type="submit" class="btn">Pay Now</button> 
                <div class="back-link" onclick="goBack()">Choose a different package</div> 
            </form> 
        </div> 
 
        <!-- Step 3: Status / Loading --> 
        <div id="step-status" style="display: none; text-align: center;"> 
            <div class="header"><h1>Processing STK Push</h1></div> 
            <div class="spinner"></div> 
            <p style="color: var(--text-muted); font-size: 0.9rem;">Check your phone and enter your 
M-Pesa PIN.</p> 
        </div> 
    </div> 
 
    <script> 
        let currentPackage = '', currentAmount = 0; 
 
        function selectPackage(id, price, name) { 
            currentPackage = id; 
            currentAmount = price; 
            document.getElementById('selected-summary').innerText = `Pay KSh ${price} for 
${name}`; 
            document.getElementById('step-packages').style.display = 'none'; 
            document.getElementById('step-form').style.display = 'block'; 
        } 
 
        function goBack() { 
            document.getElementById('step-form').style.display = 'none'; 
            document.getElementById('step-packages').style.display = 'block'; 
        } 
 
        function handlePayment(event) { 
            event.preventDefault(); 
            const phone = document.getElementById('phone').value; 
 
            document.getElementById('step-form').style.display = 'none'; 
            document.getElementById('step-status').style.display = 'block'; 
 
            fetch('stk_push.php', { 
                method: 'POST', 
                headers: { 'Content-Type': 'application/json' }, 
                body: JSON.stringify({ phone: phone, package_id: currentPackage, amount: 
currentAmount }) 
            }) 
            .then(res => res.json()) 
            .then(data => { 
                if (data.success) { 
                    alert('STK Push sent successfully! Check your phone.'); 
                } else { 
                    alert('Payment initialization failed: ' + data.message); 
                    goBack(); 
                } 
            }) 
            .catch(err => { 
                console.error(err); 
                alert('Network error occurred.'); 
                goBack(); 
            }); 
        } 
    </script> 
</body> 
</html>