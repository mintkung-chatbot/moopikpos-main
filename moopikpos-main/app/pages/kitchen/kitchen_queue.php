<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KDS - ระบบจอครัว</title>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body { font-family: 'Prompt', sans-serif; background-color: #212529; color: white; }
        
        /* สไตล์การ์ดออเดอร์ */
        .order-card {
            border: none;
            border-radius: 12px;
            overflow: hidden;
            transition: all 0.3s;
            background: #343a40;
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
        }
        .order-card:hover { transform: translateY(-5px); }
        
        /* แถบสีด้านบนบ่งบอกสถานะ */
        .status-bar { height: 6px; width: 100%; }
        .st-pending { background-color: #ffc107; } /* สีเหลือง: รอปรุง */
        .st-cooking { background-color: #0d6efd; } /* สีน้ำเงิน: กำลังปรุง */
        .st-ready { background-color: #198754; }   /* สีเขียว: รอเสิร์ฟ */

        .card-header-custom {
            padding: 10px 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: rgba(255,255,255,0.05);
        }

        .item-list { list-style: none; padding: 0; margin: 0; }
        .item-list li {
            padding: 8px 0;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            font-size: 1.1rem;
        }
        .item-list li:last-child { border-bottom: none; }
        
        .item-note { font-size: 0.85rem; color: #ff6b6b; display: block; }
        .qty-badge {
            background: #e9ecef; color: #212529;
            font-weight: bold; padding: 2px 8px; border-radius: 4px; margin-right: 8px;
        }

        /* ปุ่ม Action ใหญ่ๆ กดง่าย */
        .btn-action { width: 100%; padding: 12px; font-weight: bold; font-size: 1.1rem; border-radius: 0 0 12px 12px; }
        
        /* นาฬิกาดิจิตอล */
        .digital-clock { font-family: monospace; font-size: 1.5rem; color: #0dcaf0; }
    </style>
</head>
<body>

    <nav class="navbar navbar-dark bg-dark border-bottom border-secondary sticky-top">
        <div class="container-fluid">
            <span class="navbar-brand mb-0 h1">
                <i class="fas fa-fire-alt text-warning"></i> KDS ระบบจอครัว
            </span>
            <div class="d-flex align-items-center gap-3">
                <span id="clock" class="digital-clock">00:00:00</span>
                <a href="index.php" class="btn btn-outline-light btn-sm">กลับหน้าหลัก</a>
            </div>
        </div>
    </nav>

    <div class="container-fluid mt-4">
        <div class="row g-3" id="orders-container">
            <div class="col-12 text-center mt-5 text-muted">
                <h3><i class="fas fa-spinner fa-spin"></i> กำลังโหลดข้อมูล...</h3>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // 1. ฟังก์ชันนาฬิกา
        setInterval(() => {
            const now = new Date();
            document.getElementById('clock').innerText = now.toLocaleTimeString('th-TH');
        }, 1000);

        // 2. ฟังก์ชันโหลดออเดอร์ (ทำงานอัตโนมัติทุก 3 วิ)
        function loadOrders() {
            $.get('kitchen_api.php?action=fetch_orders', function(data) {
                renderOrders(data);
            }, 'json');
        }

        // 3. ฟังก์ชันวาดหน้าจอ (Render HTML)
        function renderOrders(orders) {
            const container = $('#orders-container');
            
            if (orders.length === 0) {
                container.html('<div class="col-12 text-center text-muted mt-5"><h1><i class="fas fa-check-circle text-success"></i></h1><h3>ไม่มีออเดอร์ค้าง</h3><p>พักผ่อนได้เชฟ!</p></div>');
                return;
            }

            let html = '';
            orders.forEach(order => {
                // กำหนดสีและข้อความปุ่มตามสถานะ
                let statusColor = 'st-pending';
                let btnClass = 'btn-warning';
                let btnText = '🔥 เริ่มปรุง';
                let statusText = 'รอคิว';
                let textColor = 'text-warning';

                if (order.status === 'cooking') {
                    statusColor = 'st-cooking';
                    btnClass = 'btn-primary';
                    btnText = '✅ ปรุงเสร็จ';
                    statusText = 'กำลังปรุง';
                    textColor = 'text-primary';
                } else if (order.status === 'ready') {
                    statusColor = 'st-ready';
                    btnClass = 'btn-success';
                    btnText = '🔔 เสิร์ฟแล้ว';
                    statusText = 'รอเสิร์ฟ';
                    textColor = 'text-success';
                }

                // สร้างรายการอาหาร
                let itemsHtml = '';
                order.items.forEach(item => {
                    let note = item.note ? `<small class="item-note">** ${item.note}</small>` : '';
                    itemsHtml += `<li>
                        <span class="qty-badge">x${item.quantity}</span> ${item.name}
                        ${note}
                    </li>`;
                });

                // ประกอบร่างการ์ด (HTML Template)
                html += `
                <div class="col-md-6 col-lg-4 col-xl-3">
                    <div class="order-card h-100">
                        <div class="status-bar ${statusColor}"></div>
                        <div class="card-header-custom">
                            <div>
                                <h5 class="mb-0 fw-bold">โต๊ะ ${order.table_no || 'กลับบ้าน'}</h5>
                                <small class="text-white-50">#${order.id} | ${order.order_type === 'takeaway' ? '🥡 ห่อกลับ' : '🍽️ ทานร้าน'}</small>
                            </div>
                            <div class="text-end">
                                <div class="${textColor} fw-bold">${statusText}</div>
                                <small class="text-white-50">${order.time_ago}</small>
                            </div>
                        </div>
                        <div class="card-body">
                            <ul class="item-list">
                                ${itemsHtml}
                            </ul>
                        </div>
                        <button class="btn ${btnClass} btn-action" onclick="updateStatus(${order.id}, '${order.status}')">
                            ${btnText}
                        </button>
                    </div>
                </div>`;
            });

            container.html(html);
        }

        // 4. ฟังก์ชันเปลี่ยนสถานะ (Action)
        function updateStatus(id, currentStatus) {
            $.post('kitchen_api.php?action=update_status', { id: id, status: currentStatus }, function(res) {
                if(res.success) {
                    loadOrders(); // โหลดใหม่ทันทีเมื่อกดเสร็จ
                }
            }, 'json');
        }

        // เริ่มทำงานครั้งแรก และตั้งเวลาวนลูป
        loadOrders();
        setInterval(loadOrders, 3000); // รีเฟรชทุก 3 วินาที

    </script>
</body>
</html>