# Color & Typography Spec · v1.0

## Màu

| Token | Hex | Dùng cho |
|---|---|---|
| Graphite nền | `#0B0C0D` | Nền chính mọi MG, transition |
| Graphite 2 | `#0F1113` / `#111315` | Nền SH23–SH24, bề mặt |
| Off-white | `#EEEDE8` | Chữ chính, nét chính |
| Neutral 2 | `#CFCEC7` | Chữ phụ |
| Muted | `#A2A29B` | Nhãn phụ, credit |
| Line | `rgba(238,237,232,.14/.28/.50)` | Lưới, trục, nét mảnh |
| Accent (vàng ấm) | `#D4A957` | **Chỉ làm tín hiệu**: nút VNISES, quỹ đạo tròn ở SH14, liên kết Nexus, vùng suy luận. ≤ 5% diện tích khung. |

Trùng với design token của vnises-gioithieu.php (`--vngt-*`) để phim và website là một hệ thống.

**Grading ảnh thật (layer A):** giữ gần dữ liệu nguồn. Chỉ chỉnh exposure/contrast toàn cục để khớp nhịp sáng giữa các shot; **không** đổi màu tổng thể sang xanh lạnh, không thêm glow, không sharpen mạnh. Ảnh khoa học có màu mã hóa (CMB, ảnh tổng hợp đa bước sóng) giữ nguyên bảng màu của tổ chức công bố.

**Footage VNISES (layer B):** không grading; giữ đúng màu UI. Có scrim graphite ở 25% dưới khung (xem 16_VNISES_Footage_Capture_List) để phụ đề đọc được.

## Typography

Font (đều SIL Open Font License 1.1, có trong `src/render/fonts/`):
- **Be Vietnam Pro** — thiết kế cho tiếng Việt, dấu thanh chuẩn. Weights 300/400/500/600.
- **IBM Plex Mono** — nhãn kỹ thuật, số, mã.

| Style | Font | Cỡ @1080 | Tracking | Dùng |
|---|---|---|---|---|
| T1 Wordmark | Be Vietnam Pro 600 | 96–104 px | 5 px | “VNISES” (SH17, SH28) |
| T2 Tên đầy đủ | Be Vietnam Pro 300 | 28–30 px | 1 px | Vietnam Nexus for Interactive Space Exploration and Science |
| T3 Nhãn kỹ thuật | IBM Plex Mono 500 | 17–19 px, VIẾT HOA | 2 px | FACT, SIMULATION, OBSERVATION… |
| T4 Nhãn MG | Be Vietnam Pro 400 | 22–30 px | 0 | Tên lĩnh vực, chú thích |
| T5 Credit ảnh | Be Vietnam Pro 400 | 18 px, Muted | 0 | “Ảnh: NASA/JPL-Caltech” — góc dưới trái (x 96, y 1000), hiện 4 s |
| T6 Phụ đề | Be Vietnam Pro 400 | ~41 px @1080 (FontSize 11 khi libass đọc SRT, PlayResY 288) | 0 | Off-white `#EEEDE8` trên hộp graphite `#0B0C0D` đục 80%, không bóng, cách đáy ~52 px. Hộp bảo đảm đọc được khi phụ đề chồng lên chữ của UI |
| T7 URL | IBM Plex Mono 400 | 24 px, Accent | 3 px | vnises.com (chỉ end card) |

Quy tắc: không font sci-fi/techno, không chữ outline, không glow, không chữ bay. Chữ trên màn hình tối thiểu; **không đặt title và phụ đề cùng lúc** (end card không có phụ đề vì chữ trùng nội dung VO).
