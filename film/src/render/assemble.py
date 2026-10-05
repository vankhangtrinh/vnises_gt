# -*- coding: utf-8 -*-
"""
Ghép offline edit theo EDL (timeline.json): 28 clip → xfade chain → fade đầu phim → temp bed
→ (tùy chọn) nhãn OFFLINE EDIT + timecode → (tùy chọn) phụ đề tiếng Việt burn-in.

Dùng: python3 assemble.py <clipsDir> <outDir> <temp_bed.wav> <vi.srt>
Mỗi clip trong clipsDir có handle 0,8 s ở hai đầu (render_mg.js / capture.js).
"""
import json, os, subprocess, sys

HERE = os.path.dirname(os.path.abspath(__file__))
TL = json.load(open(os.path.join(HERE, "timeline.json"), encoding="utf-8"))
FPS, PRE = TL["fps"], 0.8
CLIPS, OUT, BED, SRT = sys.argv[1:5]
os.makedirs(OUT, exist_ok=True)
FONT = os.path.join(HERE, "fonts", "BeVietnamPro-400.ttf")
FONTDIR = os.path.join(HERE, "fonts")
B_MAP = {"SH18": "B01", "SH19": "B02", "SH21": "B03", "SH22": "B04"}

def run(args):
    print("+", " ".join(a if len(a) < 80 else a[:77] + "..." for a in args))
    subprocess.run(args, check=True)

def x264(crf="14"):
    return ["-c:v", "libx264", "-preset", "slow", "-crf", crf, "-pix_fmt", "yuv420p", "-r", str(FPS)]

def trans(s):
    x = s["trans_in"]
    if x.startswith("dissolve:"):
        return "fade", float(x.split(":")[1])
    if x.startswith("dip:"):
        return "fadeblack", float(x.split(":")[1])
    return "cut", 1.0 / FPS  # cut sạch: khung chồng lặp duy nhất hiển thị nguyên shot sau

shots = TL["shots"]
sh = {s["id"]: s for s in shots}

ONLY_SUB = os.environ.get("ONLY_SUB") == "1"
# 1) Shot tổng hợp
comp = os.path.join(OUT, "_comp"); os.makedirs(comp, exist_ok=True)
if ONLY_SUB:  # chỉ render lại bản phụ đề (dùng _picture_1080p.mp4 đã có)
    _run_all = run
    def run(args):
        if "SubVI" in args[-1]:
            _run_all(args)
# SH24: MG (liên kết chéo) 0 → PRE+1.5 s, dissolve 0,6 s sang B05 (bắt đầu từ t = 0 của B05)
run(["ffmpeg", "-y", "-loglevel", "error", "-i", os.path.join(CLIPS, "SH24.mp4"), "-i", os.path.join(CLIPS, "B05.mp4"),
     "-filter_complex", "[0:v]trim=0:2.9,setpts=PTS-STARTPTS[a];[1:v]trim=start=0.8,setpts=PTS-STARTPTS[b];[a][b]xfade=transition=fade:duration=0.6:offset=2.3[v]",
     "-map", "[v]"] + x264("12") + [os.path.join(comp, "SH24.mp4")])
# SH26: B06a 0 → 6,8 s, dissolve 0,8 s sang B06b
run(["ffmpeg", "-y", "-loglevel", "error", "-i", os.path.join(CLIPS, "B06a.mp4"), "-i", os.path.join(CLIPS, "B06b.mp4"),
     "-filter_complex", "[0:v]trim=0:6.8,setpts=PTS-STARTPTS[a];[1:v]trim=start=0.8,setpts=PTS-STARTPTS[b];[a][b]xfade=transition=fade:duration=0.8:offset=6.0[v]",
     "-map", "[v]"] + x264("12") + [os.path.join(comp, "SH26.mp4")])

def src(sid):
    if sid in ("SH24", "SH26"):
        return os.path.join(comp, sid + ".mp4")
    if sid in B_MAP:
        return os.path.join(CLIPS, B_MAP[sid] + ".mp4")
    return os.path.join(CLIPS, sid + ".mp4")

# 2) Chuỗi xfade
inputs, filters = [], []
L = 0.0
for i, s in enumerate(shots):
    _, d_in = trans(s) if i > 0 else ("fade", 0.0)
    d_out = trans(shots[i + 1])[1] if i + 1 < len(shots) else 0.0
    start = PRE - d_in / 2
    end = PRE + s["dur"] + d_out / 2
    inputs += ["-i", src(s["id"])]
    filters.append("[%d:v]trim=start=%.4f:end=%.4f,setpts=PTS-STARTPTS,fps=%d,format=yuv420p,settb=AVTB[c%d]" % (i, start, end, FPS, i))
seglen = []
for i, s in enumerate(shots):
    d_in = trans(s)[1] if i > 0 else 0.0
    d_out = trans(shots[i + 1])[1] if i + 1 < len(shots) else 0.0
    seglen.append(s["dur"] + d_in / 2 + d_out / 2)
prev, L = "c0", seglen[0]
for i in range(1, len(shots)):
    kind, d = trans(shots[i])
    off = L - d
    tr = "custom:expr='B'" if kind == "cut" else kind
    filters.append("[%s][c%d]xfade=transition=%s:duration=%.4f:offset=%.4f[x%d]" % (prev, i, tr, d, off, i))
    prev, L = "x%d" % i, L + seglen[i] - d
filters.append("[%s]fade=t=in:st=0:d=1.6,trim=duration=%.4f[v]" % (prev, TL["total"]))
picture = os.path.join(OUT, "_picture_1080p.mp4")
run(["ffmpeg", "-y", "-loglevel", "error"] + inputs + ["-filter_complex", ";".join(filters), "-map", "[v]"] + x264("14") + [picture])
print("picture length (expected %.2f s, chain %.2f s)" % (TL["total"], L))

# 3) Bản review: nhãn OFFLINE EDIT + timecode, temp bed
label = ("drawtext=fontfile=%s:text='VNISES MANIFESTO · OFFLINE EDIT v1 · ảnh thật chưa chèn · VO chưa thu · nhạc TEMP':"
         "x=w-tw-36:y=30:fontsize=18:fontcolor=0xA2A29B@0.85" % FONT)
tcode = ("drawtext=fontfile=%s:timecode='00\\:00\\:00\\:00':rate=%d:x=w-tw-36:y=h-th-30:fontsize=18:fontcolor=0xA2A29B@0.85" % (FONT, FPS))
base = "%s,%s" % (label, tcode)
nosub = os.path.join(OUT, "VNISES_Manifesto_OfflineEdit_v1_1080p_NoSub.mp4")
run(["ffmpeg", "-y", "-loglevel", "error", "-i", picture, "-i", BED, "-filter_complex", "[0:v]%s[v]" % base,
     "-map", "[v]", "-map", "1:a"] + x264("16") + ["-c:a", "aac", "-b:a", "256k", "-ar", "48000", "-shortest", "-movflags", "+faststart", nosub])
srt_esc = SRT.replace("\\", "/").replace(":", "\\:")
# libass đọc SRT với PlayResY = 288 → FontSize 11 ≈ 41 px @1080; hộp graphite đục 80% (alpha &H33).
style = ("FontName=Be Vietnam Pro,FontSize=11,PrimaryColour=&H00E8EDEE,OutlineColour=&H330D0C0B,BackColour=&H330D0C0B,"
         "BorderStyle=3,Outline=3,Shadow=0,MarginV=14,Alignment=2")
subvi = os.path.join(OUT, "VNISES_Manifesto_OfflineEdit_v1_1080p_SubVI.mp4")
run(["ffmpeg", "-y", "-loglevel", "error", "-i", picture, "-i", BED, "-filter_complex",
     "[0:v]subtitles='%s':fontsdir='%s':force_style='%s',%s[v]" % (srt_esc, FONTDIR, style, base),
     "-map", "[v]", "-map", "1:a"] + x264("16") + ["-c:a", "aac", "-b:a", "256k", "-ar", "48000", "-shortest", "-movflags", "+faststart", subvi])
print("done")
