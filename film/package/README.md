# VNISES — Manifesto Film · Production Package v1.0

Phim tuyên ngôn chính thức của **VNISES — Vietnam Nexus for Interactive Space Exploration and Science**.
Thời lượng **00:02:55:12** · 16:9 · 25 fps · 28 shot.

## Trạng thái giao

| Hạng mục | Trạng thái |
|---|---|
| Kịch bản, VO, storyboard, shot list, EDL, phụ đề VI/EN, hướng dẫn âm thanh, motion, màu/typography, render, audit | **Hoàn chỉnh** (thư mục này) |
| Motion graphics (layer C, 9 shot) | **Đã render** — chất lượng final, dựng bằng code, tái lập được |
| Footage VNISES (layer B) | **Đã quay** từ module giới thiệu VNISES; cần quay lại từ vnises.com trước master (CR-02) |
| Ảnh/dữ liệu khoa học thật (layer A, 16 asset) | **Chưa tải** — môi trường sản xuất bị chặn mạng tới NASA/ESA/ESO/CERN; offline edit dùng slate ghi nguồn |
| Voice-over | **Chưa thu** — không có giọng đọc tổng hợp đạt yêu cầu “không robot”; script thu âm đầy đủ trong 05 |
| Nhạc | **Temp bed** tổng hợp theo cue sheet; master cần nhạc sáng tác/cấp phép |
| Video master 01–04 | **Chưa xuất** — phụ thuộc layer A + VO + nhạc. Đã xuất **offline edit** đúng timing master |

## Danh mục

| File | Nội dung |
|---|---|
| 01_Master_Script.md | Kịch bản hai cột, mốc thời gian, chỉnh sửa VO, Change Requests |
| 02_Timecoded_Storyboard.md | 13 trường cho từng shot |
| 03_Shot_List.csv | Shot list (mở được bằng Excel/Sheets) |
| 04_Asset_Register.csv | Đăng ký asset: nguồn, URL, licence, credit, trạng thái |
| 05_Voiceover_Final.txt | Lời đọc cuối, ngắt nghỉ, nhấn, phát âm, timing, hướng dẫn thu |
| 06_VNISES_manifesto_vi.srt | Phụ đề tiếng Việt (42 event) |
| 07_VNISES_manifesto_en.srt | Phụ đề tiếng Anh (43 event) |
| 08_Music_Sound_Design_Guide.md | Nguyên tắc, cue sheet có timecode, mức âm |
| 09_Motion_Graphics_Spec.md | Thông số từng shot MG |
| 10_Color_Typography_Spec.md | Bảng màu, font, style chữ |
| 11_Edit_Decision_List.csv | EDL: record/source TC, transition, VO, asset, typography, sound cue |
| 12_Render_Settings.md | Thông số xuất master + cách tái lập offline edit |
| 13_Scientific_Audit.md | Kiểm tra khoa học lời đọc và hình |
| 14_Copyright_Audit.md | Kiểm tra bản quyền/provenance, thẻ credit |
| 16_VNISES_Footage_Capture_List.md | Danh sách quay màn hình vnises.com |
| 17_Danh_sach_anh_va_nguon_tai.docx | Danh sách 16 ảnh khoa học và nguồn tải (Word), sinh bằng src/build_asset_docx.js |

Không có file 15: gói **không dùng hình ảnh AI-generated** — mọi hình minh họa khái niệm là motion graphics dựng bằng code, tất định, có nhãn loại thông tin.

## Self-audit (sau 2 vòng tự sửa)

| Gate | Kết quả | Ghi chú |
|---|---|---|
| G1 Narrative | PASS* | Đúng hành trình Câu hỏi → Khám phá → Con người là một phần vũ trụ → Tri thức qua thế hệ → VNISES → Mô hình/dữ liệu/tương tác → Nexus → Kiểm chứng → Chưa biết → VNISES. *VNISES xuất hiện ở 00:01:15:17, lệch ~5 s so với khung 40–70 s — ghi thành CR-01 kèm phương án cắt để Owner quyết. |
| G2 Scientific accuracy | PASS | Không có claim “mọi nguyên tử từ sao”; CMB, stromatolite, STM, Hubble 1929 được chú thích đúng; SH14 và SH20 là tích phân số thật, có nhãn SIMULATION (13_Scientific_Audit). |
| G3 VNISES identity | PASS | Lộ diện tiết chế (không flare/scale); footage UI thật; token màu và nhãn loại thông tin đồng bộ với website. |
| G4 Visual quality | PASS (layer B, C) | Đã soát từng khung MG và footage; sửa nhãn đè, khung lộ sơ đồ, phụ đề chồng UI. Layer A sẽ được đánh giá lại sau khi chèn ảnh thật. |
| G5 Authenticity | PASS | Không AI image, không telemetry/timestamp/số liệu giả; sơ đồ khái niệm không có trục số; footage chỉ có crop/zoom/matte/scrim. |
| G6 Asset provenance | PASS | 31 asset trong register; trạng thái trung thực (VERIFY / TO SELECT / UNKNOWN có phương án thay thế). |
| G7 Copyright | PASS (có điều kiện) | Chưa asset bên ngoài nào được dùng khi chưa rõ licence; mọi asset A phải xác minh khi tải; thẻ credit CC BY đã dựng. |
| G8 Voice-over | PASS (script) | Lời đọc có ngắt nghỉ, nhấn, phát âm, timing, hướng dẫn thu. Bản thu chưa có — không dùng TTS robot. |
| G9 Editing rhythm | PASS | 28 shot, trung bình 6,3 s; shot ngắn nhất 2,5 s (câu hỏi mở đầu); khoảng lặng 2 s sau L10, 1 s trước logo; transition chủ yếu cut/dissolve, dip-to-black chỉ ở chuyển hồi. |
| G10 Technical delivery | PASS (offline edit) | Offline edit 1080p25 đúng 100%% thời lượng timeline; SRT VI/EN ≤ 42 ký tự/dòng, ≤ 2 dòng, ≤ 18,6 ký tự/giây; CSV UTF-8 BOM. Master 01–04 chờ ảnh thật + VO + nhạc. |

## Review chống “AI cliché” — đã phát hiện và xử lý

- Cặp câu đối xứng “…không để làm đơn giản hơn. Cũng không để làm phức tạp hơn.” → gộp thành một câu (L34).
- Chuỗi năm câu song song L18–L21 giữ theo baseline, nhưng mỗi câu có một hình chứng minh riêng (footage thật/mô phỏng) và khoảng lặng khác nhau để tránh nhịp máy móc.
- Loại khỏi thiết kế: starfield, nebula nền, hạt bay, DNA phát sáng, hologram, phương trình bay, phi hành gia silhouette, “nhà khoa học nhìn màn hình”.
- Lộ diện VNISES không dùng lens flare/scale/riser; nhạc chỉ mở rộng một lần.
- Không có câu tự ca ngợi; VNISES được chứng minh bằng cách trình bày (footage lab, provenance, nhãn loại thông tin).
