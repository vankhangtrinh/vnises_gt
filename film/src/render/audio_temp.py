# -*- coding: utf-8 -*-
"""
TEMP music bed cho offline edit — KHÔNG dùng cho master.
Tổng hợp tất định (numpy) theo cue sheet trong 08_Music_Sound_Design_Guide.md để editor cảm được
nhịp và các điểm mở/rút của nhạc. Master phải dùng nhạc được sáng tác hoặc cấp phép.

Dùng: python3 audio_temp.py <out.wav>
"""
import sys, json, os, wave
import numpy as np

SR = 48000
HERE = os.path.dirname(os.path.abspath(__file__))
TL = json.load(open(os.path.join(HERE, "timeline.json"), encoding="utf-8"))
TOTAL = TL["total"]
N = int(TOTAL * SR)
t = np.arange(N) / SR
shot = {s["id"]: s for s in TL["shots"]}
line = {l["id"]: l for l in TL["lines"]}

def env(points):
    """Đường bao tuyến tính theo các điểm (thời gian giây, mức tuyến tính)."""
    xs = [p[0] for p in points]; ys = [p[1] for p in points]
    return np.interp(t, xs, ys)

def db(x):
    return 10 ** (x / 20.0)

def tone(freq, detune=0.0, phase=0.0):
    return 0.5 * (np.sin(2 * np.pi * freq * t + phase) + np.sin(2 * np.pi * freq * (1 + detune) * t + phase * 1.7))

def onepole_lp(x, fc):
    a = np.exp(-2 * np.pi * fc / SR)
    y = np.empty_like(x); acc = 0.0
    # vectorized IIR via cumulative trick is complex; chunked loop is fine for 3 minutes
    for i in range(0, len(x), 4096):
        seg = x[i:i + 4096]
        out = np.empty_like(seg)
        for j, v in enumerate(seg):
            acc = (1 - a) * v + a * acc
            out[j] = acc
        y[i:i + 4096] = out
    return y

rng = np.random.default_rng(1729)  # tất định
T = lambda sid: shot[sid]["tin"]

# Room tone: nhiễu lọc thấp, rất nhỏ, có suốt phim trừ 1 s im lặng trước end card
noise = onepole_lp(rng.standard_normal(N).astype(np.float64), 700.0)
noise /= np.max(np.abs(noise)) + 1e-9
room = noise * env([(0, 0), (1.0, db(-52)), (T("SH28") + 0.2, db(-52)), (T("SH28") + 0.6, 0), (TOTAL, 0)])

# Drone thấp (A1 + E2), dao động biên độ rất chậm
lfo = 0.85 + 0.15 * np.sin(2 * np.pi * 0.05 * t)
drone = (tone(55.0, 0.0015) + 0.6 * tone(82.41, 0.002)) * lfo
drone_env = env([(0, 0), (2.0, db(-34)), (T("SH05"), db(-32)), (T("SH11"), db(-33)), (T("SH12"), db(-38)),
                 (T("SH13"), db(-33)), (T("SH17"), db(-30)), (T("SH25"), db(-34)), (T("SH27"), db(-35)),
                 (T("SH28") - 0.3, db(-40)), (T("SH28") + 0.3, 0), (TOTAL, 0)])

# Pad (A2 · E3 · B3) — vào ở L06, rút ở SH11, mở lại SH16, rút ở SH25
pad = (tone(110.0, 0.003) + 0.7 * tone(164.81, 0.0025, 0.4) + 0.45 * tone(246.94, 0.002, 1.1))
pad_env = env([(0, 0), (line["L06"]["start"] - 0.5, 0), (line["L06"]["start"] + 3, db(-36)), (T("SH11"), db(-38)),
               (T("SH11") + 2, db(-46)), (T("SH16"), db(-40)), (T("SH17") + 0.5, db(-31)), (T("SH18") + 2, db(-34)),
               (T("SH23"), db(-35)), (T("SH25"), db(-38)), (T("SH25") + 3, db(-46)), (T("SH26"), db(-44)),
               (T("SH27"), db(-50)), (T("SH27") + 3, 0), (TOTAL, 0)])

# Bè cao (E4 · F#4) — chỉ ở đoạn lộ diện VNISES và mỗi chuỗi Nexus mới
high = 0.5 * tone(329.63, 0.001) + 0.35 * tone(369.99, 0.0012, 0.7)
hi_pts = [(0, 0), (T("SH16"), 0), (T("SH17") + 0.8, db(-42)), (T("SH18") + 1.5, db(-50)), (T("SH19"), 0)]
for lid in ("L24", "L25", "L26"):
    s0 = line[lid]["start"]
    hi_pts += [(s0 - 0.2, 0), (s0 + 0.8, db(-50)), (s0 + 3.0, 0)]
hi_pts += [(TOTAL, 0)]
hi_pts.sort(key=lambda p: p[0])
high_env = env(hi_pts)

# Nốt kết (A2 + E3) sau 1 s im lặng của end card
end0 = T("SH28") + 1.2
fin = (tone(110.0, 0.002) + 0.6 * tone(164.81, 0.002)) * np.where(t >= end0, np.exp(-np.clip(t - end0, 0, None) / 3.5) * (1 - np.exp(-np.clip(t - end0, 0, None) / 0.6)), 0) * db(-34)

mix = room + drone * drone_env + pad * pad_env + high * high_env + fin
mix = onepole_lp(mix, 3500.0)
# Fade in/out mềm
mix *= np.clip(t / 1.5, 0, 1) * np.clip((TOTAL - t) / 1.0, 0, 1)
peak = np.max(np.abs(mix))
mix = mix / peak * db(-9)
left = mix
right = np.roll(mix, int(0.011 * SR)) * 0.98  # độ rộng stereo rất nhẹ
st = np.stack([left, right], axis=1)
pcm = (np.clip(st, -1, 1) * 32767).astype(np.int16)
with wave.open(sys.argv[1], "wb") as w:
    w.setnchannels(2); w.setsampwidth(2); w.setframerate(SR); w.writeframes(pcm.tobytes())
print("wrote", sys.argv[1], round(TOTAL, 2), "s")
