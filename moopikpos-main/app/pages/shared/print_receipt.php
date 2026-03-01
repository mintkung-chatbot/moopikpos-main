<!DOCTYPE html>
<html>
<head>
    <style>
        /* ซ่อนทุกอย่างในหน้าเว็บ */
        body { visibility: hidden; }
        
        /* แสดงเฉพาะส่วนใบเสร็จเมื่อสั่งพิมพ์ */
        #receipt-area {
            visibility: visible;
            position: absolute;
            left: 0;
            top: 0;
            width: 58mm; /* ขนาดมาตรฐานเครื่องพิมพ์ใบเสร็จ */
            font-family: 'Courier New', monospace; /* ฟอนต์แบบเครื่องพิมพ์ดีด */
            font-size: 12px;
        }
        
        @media print {
            @page { margin: 0; }
            body { margin: 0.5cm; }
        }
        
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .line { border-bottom: 1px dashed black; margin: 5px 0; }
    </style>
</head>
<body onload="window.print()">

<div id="receipt-area">
    <div class="text-center">
        <strong>ร้านอาหารตามสั่ง IT Gen</strong><br>
        TAX ID: 1234567890<br>
        --------------------------------
    </div>
    
    <div>โต๊ะ: 5 | วันที่: <?php echo date('d/m/Y H:i'); ?></div>
    <div class="line"></div>
    
    <table width="100%">
        <tr>
            <td>กะเพราหมู</td>
            <td class="text-right">50</td>
        </tr>
        <tr>
            <td>ไข่ดาว</td>
            <td class="text-right">10</td>
        </tr>
    </table>
    
    <div class="line"></div>
    <div class="text-right">
        <strong>รวมทั้งสิ้น: 60.00 บาท</strong>
    </div>
    <br>
    <div class="text-center">ขอบคุณที่ใช้บริการ</div>
</div>

</body>
</html>