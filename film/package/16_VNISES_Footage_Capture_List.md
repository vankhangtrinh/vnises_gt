# VNISES Footage Capture List · v1.0

**Trạng thái:** footage trong offline edit được quay từ module giới thiệu `vnises-gioithieu.php` (repo `vankhangtrinh/vnises_gt`, commit 9d59dd4) render cục bộ, bằng script `src/render/capture.js`. Môi trường sản xuất không truy cập được vnises.com, nên **chưa xác nhận** các tính năng nào đang chạy trên site. Trước khi phát hành master, quay lại mọi shot dưới đây **từ vnises.com thật**. Nếu một tính năng chưa có trên site, dùng phương án thay thế ghi trong bảng; **không** dựng giả một tính năng chưa tồn tại.

## Thiết lập quay chung

- Trình duyệt Chromium/Chrome bản ổn định, profile sạch (không extension, không bookmark bar), zoom 100%.
- Viewport **1920×1080** CSS px, **devicePixelRatio 2** (quay 3840×2160) để push-in số không vỡ.
- 60 fps nếu quay màn hình thời gian thực (OBS: CQP 12, keyframe 1 s) hoặc 25 fps khung-theo-khung bằng `capture.js` (khuyến nghị — tất định).
- Ẩn con trỏ hệ điều hành; dùng con trỏ overlay của capture.js (mũi tên trắng viền đen, 26×34 px) để thao tác nhất quán.
- Không thông báo, không cookie banner trong khung. Không có dữ liệu cá nhân trên màn hình.
- Không chỉnh sửa UI; chỉ crop/zoom, một matte tối ở mép khi cần che phần không liên quan (ghi rõ trong EDL), và **scrim vùng phụ đề**: gradient graphite ở 270 px dưới khung (0 → 82% tại 62% chiều cao scrim → 90%) để phụ đề luôn đọc được trên nền UI nhiều chữ.

| ID | Shot | Trang / module | Thao tác | Thời lượng | Crop / camera | Phương án thay thế |
|---|---|---|---|---|---|---|
| B01 | SH18 | Trang giới thiệu VNISES — scene 01 (tiêu đề + hình quỹ đạo Kepler II) | Không click. Hình quỹ đạo chạy animation tự nhiên. | 5,6 s + handle 0,8 s mỗi đầu | Giữ tiêu đề 1 s → trượt tới hình quỹ đạo (1,0–3,4 s) → push-in 1,00 → 1,55 | Nếu có module quan sát Mặt Trời thật (B07), dùng cho câu L18 |
| B02 | SH19 | Scene 04 — lab độ lệch tâm | Đặt e = 0,10; kéo thanh trượt tới 0,70 trong 0,35–2,5 s, easing | 3,0 s + handle | Zoom 1,25 vào khối lab, thấy nhãn SIMULATION | Bất kỳ mô phỏng có tham số trên vnises.com, nếu nhãn loại thông tin hiển thị |
| B03 | SH21 | Scene 04 — lab độ lệch tâm | e = 0,30 → bấm “Ghim làm quỹ đạo tham chiếu” (0,6 s) → kéo e lên 0,75 (1,05–2,6 s) | 3,0 s + handle | Như B02 | — |
| B04 | SH22 | Scene 03 — khối “Một câu hỏi, nhiều hướng đi” | Không click; trượt dọc chậm | 3,8 s + handle | Zoom 1,85, matte trái 430 px | — |
| B05 | SH24 | Scene 03 — sơ đồ Nexus | Con trỏ đi tới “Mô hình”, click ở 0,9 s; bảng quan hệ đổi sang “Mô hình ↔ Thực tế” | 3,2 s + handle | Zoom 1,08 vào sơ đồ + bảng quan hệ | Bản đồ Nexus/lĩnh vực trên vnises.com |
| B06a | SH26 | Scene 07 — “Ví dụ cấu trúc provenance” | Trượt dọc chậm | 6,6 s | Zoom 1,2 | Trang phương pháp/nguồn của vnises.com nếu có |
| B06b | SH26 | Scene 04 — chuỗi Quan sát → Thay đổi → So sánh → Kiểm chứng → Hiểu | Không click | 6,6 s | Push-in 1,25 → 1,38 | — |
| B07 | SH18 (thay thế) | vnises.com — Trạm Vũ trụ / quan sát Mặt Trời (chỉ khi đang hoạt động) | Mở module, chờ ảnh SDO tải xong; không cuộn | 3 s | Khung gồm ảnh + nguồn + thời điểm dữ liệu đúng như UI | Giữ B01 |

**Tuyệt đối không:** quay ảnh/dữ liệu “live” rồi dựng lại timestamp; chèn số liệu vào UI; quay một trang dàn dựng giả làm trang thật; gọi tính năng chưa phát hành là đang hoạt động.
