# Music & Sound Design Guide · v1.0

## Nguyên tắc

- Nhạc **đứng sau lời đọc**: ambient cinematic tối giản, phát triển chậm. Không epic orchestra, không trailer percussion, không boom/riser, không corporate uplifting.
- Hòa âm: quãng năm và quãng hai trên nền A (A–E–B, thêm F# ở bè cao). Tránh hợp âm trưởng đầy đủ (cảm giác “uplifting”).
- Sound design rất tiết chế: room tone, low-frequency texture, tối đa một “data tone”. **Không** laser, whoosh liên tục, beep sci-fi, tiếng động cơ tàu vũ trụ, âm thanh HUD, tiếng click chuột giả.
- Đường cong: rất nhẹ ở đầu → mở rộng một chút ở lộ diện VNISES (SH17) → rút lại ở Scientific Integrity (SH25) → tối giản ở kết.

## Cue sheet

| Cue | Shot | Timecode | Mô tả |
|---|---|---|---|
| M1 | SH01 | 00:00:00:00 | Room tone rất thấp + drone A1/E2 bắt đầu từ im lặng (−34 dBFS ref). Không giai điệu. |
| M2 | SH05 | 00:00:19:17 | Pad thấp (A2·E3·B3) vào dần từ câu L06 'Khoa học đã đưa con người…'. |
| M3 | SH06 | 00:00:23:14 | Một 'data tone' rất nhẹ (sine 880 Hz, attack 20 ms, decay 600 ms, −30 dB so với VO). Chỉ một lần. |
| M4 | SH11 | 00:00:38:23 | Nhạc rút còn một bè; pad giảm ~8 dB trong 2 s. |
| M5 | SH12 | 00:00:47:07 | 2 giây sau 'chưa bao giờ đứng ngoài tự nhiên': chỉ còn room tone + một nốt trầm duy nhất. |
| M6 | SH16 | 00:01:10:16 | Pad mở lại dần theo mạng lưới lớn lên. |
| M7 | SH17 | 00:01:15:17 | Điểm mở rộng duy nhất của nhạc: hợp âm mở (quãng năm + quãng hai, không có quãng ba trưởng), thêm bè cao E4·F#4 rất nhẹ. Không trống, không boom, không riser. |
| M8 | SH23 | 00:01:39:19 | Mỗi chuỗi Nexus mới (L24, L25, L26): một chuyển động hòa âm nhỏ ở bè cao, ≤ 3 s. |
| M9 | SH25 | 00:02:00:18 | Rút về một lớp trầm; không âm thanh minh họa cho từng nhãn. |
| M10 | SH27 | 00:02:32:08 | Một âm trầm duy nhất, tắt hẳn trước end card. |
| M11 | SH28 | 00:02:41:21 | Im lặng tuyệt đối 1,0 s, sau đó một nốt kết rất nhẹ (A2+E3), decay ~3,5 s. |

## Mức âm (web master)

| Thành phần | Mục tiêu |
|---|---|
| Integrated loudness toàn phim | −16 LUFS (±1), True Peak ≤ −1 dBTP |
| VO | đỉnh lời đọc ~ −18 đến −16 LUFS short-term |
| Nhạc dưới VO | thấp hơn VO 18–22 dB |
| Nhạc ở khoảng lặng | được nâng 3–6 dB, không vượt mức VO trung bình |
| Bản broadcast (nếu cần) | −23 LUFS (EBU R128) |

## Nguồn nhạc

- **Ưu tiên:** nhạc sáng tác riêng (work-for-hire, VNISES sở hữu bản quyền).
- **Phương án 2:** thư viện có licence rõ cho sử dụng trên web/mạng xã hội/sự kiện, lưu chứng từ licence cùng gói.
- **Không** dùng nhạc trên YouTube Audio Library hay nhạc “no copyright” không có chứng từ.
- Brief cho composer: tempo tự do (rubato) hoặc ~60 BPM không có phách rõ; pad + piano chuẩn bị (prepared/felt piano) hoặc đàn dây chơi sul tasto rất nhẹ; một chuyển động hòa âm duy nhất ở SH17.

## Temp bed trong offline edit

`renders/` dùng một temp bed tổng hợp tất định (`src/render/audio_temp.py`) theo đúng cue sheet trên để editor cảm nhịp. **Không dùng cho master.**
