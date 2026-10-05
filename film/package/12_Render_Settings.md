# Render Settings · v1.0

## Master (sau khi có ảnh thật + VO)

| Mục | Giá trị |
|---|---|
| Timeline | 3840×2160 (UHD), 25 fps, progressive, Rec.709, gamma 2.4 |
| Mezzanine | ProRes 422 HQ, 10-bit, PCM 48 kHz/24-bit stereo |
| `01_VNISES_Manifesto_Master_4K.mp4` | H.264 High@5.1, 3840×2160, 25p, 45–60 Mb/s VBR 2-pass, AAC-LC 320 kb/s 48 kHz, −16 LUFS / −1 dBTP |
| `02_VNISES_Manifesto_Master_1080p.mp4` | H.264 High@4.2, 1920×1080, 25p, 16–20 Mb/s, AAC 320 kb/s |
| `03_VNISES_Manifesto_No_Subtitle.mp4` | như 02, không phụ đề |
| `04_VNISES_Manifesto_Subtitled_VI.mp4` | như 02, burn-in `06_VNISES_manifesto_vi.srt` theo style T6 |
| Social 9:16 / 1:1 | Recut riêng (không crop máy móc): MG dựng lại ở 1080×1920 / 1080×1080, phụ đề lớn hơn 15%; CTA chỉ có ở bản social |

Tham số mẫu (ffmpeg) cho 02:

```
ffmpeg -i master_prores.mov -c:v libx264 -profile:v high -level 4.2 -pix_fmt yuv420p \
  -b:v 18M -maxrate 24M -bufsize 36M -r 25 -c:a aac -b:a 320k -ar 48000 -movflags +faststart 02_VNISES_Manifesto_Master_1080p.mp4
```

## Offline edit trong gói này

`src/render/` render toàn bộ phim từ mã nguồn:

```
cd film/src && python3 build_docs.py                       # tài liệu + timeline.json
cd render && php module_page.php > vnises_page.html         # trang module VNISES để quay footage
node render_mg.js ../../renders/clips 1                    # layer C + slate layer A (1080p)
node capture.js   ../../renders/clips 1                    # footage layer B
python3 audio_temp.py ../../renders/temp_bed.wav           # TEMP music bed
python3 assemble.py                                        # ghép theo EDL → renders/*.mp4
```

DPR 2 (`node render_mg.js <dir> 2`) cho plate 2160p của layer C.
