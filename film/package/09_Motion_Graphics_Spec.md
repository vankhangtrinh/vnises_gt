# Motion Graphics Spec · v1.0

Toàn bộ motion graphics (layer C) được dựng bằng code, tất định (không `Math.random`), render lại được từng khung: `src/render/film.html` + `src/render/render_mg.js`. Không dùng hình ảnh AI-generated.

## Quy tắc chung

- Canvas 1920×1080 logic; render 2160p bằng DPR 2 (vector, không upscale).
- Vùng an toàn: title-safe 90%. **Không đặt nhãn dưới y = 880 px** (vùng phụ đề).
- Chuyển động cho phép: fade (cubic in-out 0,5–1,3 s), vẽ nét (stroke reveal), trượt camera chậm. Không bounce, không overshoot, không particle, không glow.
- Mỗi hình mang nhãn loại thông tin góc trên trái (120, 96): `FACT`, `SIMULATION`, `SƠ ĐỒ KHÁI NIỆM`, `NEXUS` — đồng bộ với hệ nhãn trên vnises.com.
- Nét: 1,2–2,4 px @1080. Màu chỉ lấy từ bảng màu trong 10_Color_Typography_Spec.

## SH13 — Nguyên tố (FACT)

- Sáu ký hiệu: H(1), O(8), C(6), N(7), Ca(20), P(15) tại x = 520, 860, 1030, 1200, 1370, 1540; y = 470; cỡ 112 px, weight 300. Số hiệu nguyên tử 20 px mono, lệch trái-trên.
- Xuất hiện lần lượt từ 0,45 s, cách 0,32 s, fade 0,7 s.
- Ngoặc nối: H → “vũ trụ sơ khai”; O·C·N·Ca·P → “các ngôi sao và vụ nổ sao”. Vẽ nét 1,1 s từ 2,55 s.
- **Không** hiển thị phần trăm khối lượng; không dùng “mọi nguyên tử đến từ sao”.

## SH14 — Khẩu pháo của Newton (SIMULATION)

- Tích phân RK4 định luật hấp dẫn Newton, đơn vị chuẩn hóa GM = 1, bán kính phóng r₀ = 1; Trái Đất R = 0,80 r₀ (ngọn núi phóng đại — có ghi chú trên hình).
- Phóng ngang với v/v_tròn = 0,70 · 0,85 · 0,93 (rơi chạm đất, xa dần) và 1,00 (quỹ đạo tròn, màu accent).
- Mỗi đường vẽ theo tiến trình tích phân (0,7–1,9 s/đường), một chấm sáng ở đầu đường đang vẽ.

## SH16 → SH17 — Mạng tri thức → VNISES

- 6 thế hệ (cột) × 4–9 nút, vị trí tất định (LCG seed 20240613). Mỗi nút nối với 1–3 nút thế hệ trước gần nhất.
- Thế hệ đầu gắn nhãn 1610 · 1609 · 1687 · 1929 (bốn tư liệu ở SH15), mờ dần sau 1,6 s.
- Nút VNISES (accent) xuất hiện **cuối câu L15** (“…và bổ sung một phần mới”) — VNISES là một phần mới của mạng, không phải trung tâm của nó.
- SH17: camera trượt 2,2 s đưa nút VNISES về (960, 400); mạng mờ 80%. Wordmark “VNISES” 104 px/600, tracking 10 → 5 px, fade 1,3 s từ 1,1 s; tên đầy đủ 30 px/300 từ 2,3 s. Không scale, không flare, không glow.

## SH20 — Kiểm tra giả định (SIMULATION, định tính)

- Vật ném với v₀ như nhau, góc 45°. Đường 1 (nét đứt): không lực cản. Đường 2 (accent): lực cản ∝ v² — tầm xa ngắn hơn, đỉnh thấp hơn, nhánh rơi dốc hơn nhánh lên.
- Nhãn `ASSUMPTION / bỏ qua lực cản không khí` → gạch ngang ở 1,3 s → `có lực cản không khí`.

## SH23 — Nexus build

- Pha 1 (L23): 9 lĩnh vực trong 9 ô (420×150), vạch ô mờ dần trước khi câu kết thúc.
- Pha 2: ba hàng ngang y = 300 / 500 / 700, nút cách đều x = 330 → 1590:
  1. Ánh sáng → Thế giới tự nhiên → Lượng tử → Thiên văn học (L24)
  2. Chuyển động → Cơ học → Quỹ đạo → Du hành không gian (L25)
  3. Quan sát thiên hà → Vũ trụ học → Lịch sử vũ trụ (L26; nhãn đặt dưới nút)
- Mỗi nút: fade 0,55 s, cách 0,6 s; mũi tên nhỏ khi đoạn nối hoàn tất.

## SH24 — Nexus

- Bốn liên kết chéo (Bézier bậc 2, accent, 1,8 px) vẽ lần lượt 0–1,6 s: Thế giới tự nhiên ↔ Cơ học · Quỹ đạo ↔ Thiên văn học · Lượng tử ↔ Vũ trụ học · Thiên văn học ↔ Lịch sử vũ trụ. Đường cong vòng tránh nhãn.
- Dissolve 0,6 s sang footage sơ đồ Nexus thật (B05).

## SH25 — Scientific integrity (SƠ ĐỒ KHÁI NIỆM)

- Trục không có số, không đơn vị. Đường mô hình y = 760 − 430·(1 − e^(−(x−300)/420)) chỉ để tạo hình dạng.
- Thứ tự theo VO: L29 DATA (ô vuông rỗng + thanh sai số) + SOURCE · L30 OBSERVATION (chấm đặc) + SIMULATION (đường nét đứt) · L31 ranh giới chấm (x = 1180) + ASSUMPTION/CONDITIONS · L32 vùng gạch chéo + LIMITATIONS · L33 vùng bất định mở rộng (accent 14%) + INFERENCE.
- Tĩnh: không camera move; chỉ fade và vẽ nét.

## SH28 — End card

- Nền #000. Im lặng 1,2 s → “VNISES” 96 px/600 (y 500) → tên đầy đủ 28 px/300 (y 578) → “vnises.com” 24 px mono accent (y 660); mỗi dòng fade 1,0 s, cách 1,0 s.
- Thẻ credit hình ảnh 4,4 s cuối (xem 14_Copyright_Audit). Không CTA.

## Slate layer A (chỉ trong offline edit)

Shot ảnh thật hiển thị slate: mã asset, mô tả, nguồn, chuyển động dự kiến (khung vàng mô phỏng push-in), trạng thái licence và dòng credit sẽ hiển thị. Thay slate bằng ảnh thật theo 04_Asset_Register.csv, giữ nguyên thời lượng và chuyển động.
