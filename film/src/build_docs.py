# -*- coding: utf-8 -*-
"""Sinh toàn bộ production package (film/package/) từ film_data.py + timeline.py.
Chạy: python3 build_docs.py
"""
import csv, io, os, re, textwrap
import film_data as D
import timeline as TLM

HERE = os.path.dirname(os.path.abspath(__file__))
OUT = os.path.normpath(os.path.join(HERE, "..", "package"))
os.makedirs(OUT, exist_ok=True)
LINES, SHOTS, TOTAL = TLM.build()
LBY = {l["id"]: l for l in LINES}
SBY = {s["id"]: s for s in SHOTS}
ABY = {a["id"]: a for a in D.ASSETS}
tc = TLM.tc

def write(name, text):
    with open(os.path.join(OUT, name), "w", encoding="utf-8", newline="") as f:
        f.write(text)

def srt_time(t):
    ms = int(round(t * 1000))
    h, ms = divmod(ms, 3600000); m, ms = divmod(ms, 60000); s, ms = divmod(ms, 1000)
    return "%02d:%02d:%02d,%03d" % (h, m, s, ms)

def trans(s):
    x = s["trans_in"]
    if x.startswith("dissolve:"):
        return "Dissolve", float(x.split(":")[1])
    if x.startswith("dip:"):
        return "Dip to black", float(x.split(":")[1])
    if x.startswith("fade from black"):
        return "Fade from black", 1.6
    return "Cut", 0.0

# ---------------------------------------------------------------------------
# Sound cues (dùng chung cho 08, 11)
# ---------------------------------------------------------------------------
CUES = [
    ("M1", "SH01", "Room tone rất thấp + drone A1/E2 bắt đầu từ im lặng (−34 dBFS ref). Không giai điệu."),
    ("M2", "SH05", "Pad thấp (A2·E3·B3) vào dần từ câu L06 'Khoa học đã đưa con người…'."),
    ("M3", "SH06", "Một 'data tone' rất nhẹ (sine 880 Hz, attack 20 ms, decay 600 ms, −30 dB so với VO). Chỉ một lần."),
    ("M4", "SH11", "Nhạc rút còn một bè; pad giảm ~8 dB trong 2 s."),
    ("M5", "SH12", "2 giây sau 'chưa bao giờ đứng ngoài tự nhiên': chỉ còn room tone + một nốt trầm duy nhất."),
    ("M6", "SH16", "Pad mở lại dần theo mạng lưới lớn lên."),
    ("M7", "SH17", "Điểm mở rộng duy nhất của nhạc: hợp âm mở (quãng năm + quãng hai, không có quãng ba trưởng), thêm bè cao E4·F#4 rất nhẹ. Không trống, không boom, không riser."),
    ("M8", "SH23", "Mỗi chuỗi Nexus mới (L24, L25, L26): một chuyển động hòa âm nhỏ ở bè cao, ≤ 3 s."),
    ("M9", "SH25", "Rút về một lớp trầm; không âm thanh minh họa cho từng nhãn."),
    ("M10", "SH27", "Một âm trầm duy nhất, tắt hẳn trước end card."),
    ("M11", "SH28", "Im lặng tuyệt đối 1,0 s, sau đó một nốt kết rất nhẹ (A2+E3), decay ~3,5 s."),
]
CUE_BY_SHOT = {}
for cid, sid, desc in CUES:
    CUE_BY_SHOT.setdefault(sid, []).append(cid)

TYPO = {
    "C": "T3 nhãn mono + T4 nhãn sans (xem 10_Color_Typography_Spec)",
    "A": "T5 credit line",
    "B": "Không thêm chữ ngoài UI",
}

# ---------------------------------------------------------------------------
# 05 Voice-over final
# ---------------------------------------------------------------------------
def vo_txt():
    o = ["VNISES — MANIFESTO FILM · VOICE-OVER FINAL · %s" % D.VERSION,
         "Ngôn ngữ: tiếng Việt · Thời lượng VO ước tính: %s · Tổng phim: %s" % (tc(LINES[-1]["end"]), tc(TOTAL)),
         "",
         "KÝ HIỆU",
         "  /      ngắt hơi ngắn (~0,25 s)",
         "  //     ngắt dài (~0,6–1,0 s)",
         "  [n s]  khoảng lặng có chủ đích, không đọc",
         "  *từ*   nhấn nhẹ (không nhấn kịch tính)",
         "  ↘      hạ giọng tự nhiên cuối câu",
         "",
         "GIỌNG: trưởng thành, ấm, trầm vừa; đọc như đang giải thích cho một người cụ thể ngồi đối diện.",
         "Không 'trailer voice', không ngân cuối câu, không cường điệu. Tốc độ mục tiêu 3,9–4,3 âm tiết/giây;",
         "các câu then chốt (L10, L11, L27, L36, L37) chậm hơn (3,3–3,8).",
         "",
         "[%s s — im lặng mở đầu]" % ("%.1f" % D.LEAD_IN).replace(".", ","), ""]
    marks = {
        "L01": "Có những câu hỏi / đã đi cùng con người / từ rất lâu. ↘",
        "L04": "*Vật chất*, / *không gian* / và *thời gian* / được tổ chức bởi những quy luật nào?",
        "L06": "Khoa học / đã đưa con người tiến rất xa / trong việc trả lời những câu hỏi ấy.",
        "L08": "Nhưng mỗi khám phá mới / lại mở ra / những câu hỏi mới. ↘",
        "L10": "Nhưng con người / *chưa bao giờ* / đứng ngoài tự nhiên. ↘",
        "L11": "Vật chất tạo nên chúng ta / cũng là vật chất / của vũ trụ.",
        "L14": "Nó được xây dựng qua nhiều thế hệ — / từ quan sát, / câu hỏi, / thí nghiệm, / mô hình, / dữ liệu, / từ những sai lầm được nhận ra / và những hiểu biết / được *sửa lại*.",
        "L16": "VNISES / được xây dựng với mong muốn / đóng góp *một phần* / vào hành trình ấy.",
        "L22": "Và một câu hỏi / có thể dẫn sang / một câu hỏi sâu hơn.",
        "L27": "Đó là ý nghĩa / của Nexus.",
        "L33": "Và khi chưa có đủ bằng chứng, / sự chưa chắc chắn / cũng cần được nói rõ.",
        "L35": "Mục tiêu / là làm cho con đường từ quan sát / đến hiểu biết / trở nên rõ ràng hơn.",
        "L36": "Có thể sẽ luôn còn những câu hỏi / vượt quá hiểu biết hiện tại / của chúng ta.",
        "L37": "Và chính vì thế, / hành trình khám phá / vẫn tiếp tục. ↘",
        "L38": "VNISES. // Vietnam Nexus for Interactive Space Exploration and Science.",
    }
    for l in LINES:
        o.append("%s  [%s → %s]  (%s s)" % (l["id"], tc(l["start"]), tc(l["end"]), ("%.1f" % l["dur"]).replace(".", ",")))
        o.append("  " + marks.get(l["id"], l["vi"]))
        if l["note"]:
            o.append("  Ghi chú: " + l["note"])
        if l["gap"] >= 0.6:
            o.append("  [%s s]" % ("%.1f" % l["gap"]).replace(".", ","))
        o.append("")
    o += ["PHÁT ÂM",
          "  VNISES      — Owner xác nhận cách đọc chính thức (CR-03). Mặc định đề xuất: đọc liền như một từ ‘Vi-ni-xít’ /ˈviː.nɪ.sɪs/;",
          "                giữ nhất quán ở L16, L34, L38.",
          "  Nexus       — /ˈnɛk.səs/ ‘néc-xợt’; không đọc ‘nếch-xút’.",
          "  Vietnam Nexus for Interactive Space Exploration and Science — tiếng Anh chuẩn, nhịp đều, không nhấn kiểu quảng cáo.",
          "  quỹ đạo, lượng tử, thiên văn học — đọc rõ dấu ngã/hỏi; không nuốt âm.",
          "",
          "THU ÂM",
          "  48 kHz / 24-bit WAV, mono, phòng thu khô (RT60 < 0,3 s), micro condenser màng lớn cách 20–25 cm có pop filter.",
          "  Thu 2–3 take mỗi câu; giữ 1 s room tone đầu và cuối mỗi file. Đặt tên: VNISES_VO_L01_take2.wav.",
          "  Mục tiêu sau xử lý: −16 LUFS (dialog-gated) cho web master; True Peak ≤ −1 dBTP.",
          ""]
    return "\n".join(o)

# ---------------------------------------------------------------------------
# SRT
# ---------------------------------------------------------------------------
def split_two(text, maxlen=42):
    words = text.split(" ")
    if len(text) <= maxlen:
        return [text]
    best = None
    for i in range(1, len(words)):
        a, b = " ".join(words[:i]), " ".join(words[i:])
        if len(a) <= maxlen and len(b) <= maxlen:
            score = abs(len(a) - len(b)) - (8 if a.endswith((",", "—", ";")) else 0)
            if best is None or score < best[0]:
                best = (score, [a, b])
    return best[1] if best else textwrap.wrap(text, maxlen)

def chunks(text, maxlen=42):
    """Chia câu dài thành các event ≤ 2 dòng, ưu tiên ngắt ở dấu câu."""
    if len(text) <= 2 * maxlen:
        return [text]
    parts = re.split(r"(?<=[,;—])\s+", text)
    out, cur = [], ""
    for p in parts:
        cand = (cur + " " + p).strip()
        if len(cand) <= 2 * maxlen - 6:
            cur = cand
        else:
            if cur:
                out.append(cur)
            cur = p
    if cur:
        out.append(cur)
    final = []
    for p in out:  # đoạn vẫn quá dài (không có dấu câu) → chia đôi theo từ
        while len(p) > 2 * maxlen - 6:
            w = p.split(" "); h = len(w) // 2
            final.append(" ".join(w[:h])); p = " ".join(w[h:])
        final.append(p)
    return final

def srt(lang):
    events = []
    for i, l in enumerate(LINES):
        if l["id"] == "L38":
            continue  # trùng với chữ trên end card — không để phụ đề và title cạnh tranh
        text = l["vi"] if lang == "vi" else l["en"]
        parts = chunks(text)
        total_chars = sum(len(p) for p in parts)
        t0, t1 = l["start"], l["end"]
        nxt = LINES[i + 1]["start"] if i + 1 < len(LINES) else TOTAL
        end_ext = min(t1 + 0.45, nxt - 0.08)
        acc = t0
        for j, p in enumerate(parts):
            dur = (t1 - t0) * len(p) / total_chars
            s, e = acc, (acc + dur if j < len(parts) - 1 else end_ext)
            if e - s < 1.0:
                e = min(s + 1.0, nxt - 0.08)
            events.append((s, e, "\n".join(split_two(p))))
            acc += dur
    o = []
    for k, (s, e, txt) in enumerate(events, 1):
        o += [str(k), "%s --> %s" % (srt_time(s), srt_time(e)), txt, ""]
    return "\n".join(o), events

# ---------------------------------------------------------------------------
# 01 Master script
# ---------------------------------------------------------------------------
def master_script():
    o = ["# VNISES — Manifesto Film · Master Script %s" % D.VERSION, "",
         "**Định dạng:** Scientific institutional manifesto film · 16:9 · 25 fps · **thời lượng %s** (%.1f s)" % (tc(TOTAL), TOTAL), "",
         "**Trạng thái:** VO là bản cuối về nội dung. Timecode là mục tiêu, tính từ tốc độ đọc 3,5–4,4 âm tiết/giây; khi có VO thu thật, editor conform theo waveform. Thứ tự shot, nội dung và transition giữ nguyên.", "",
         "## Hành trình kể chuyện", "",
         "| Hồi | Shot | Timecode |", "|---|---|---|"]
    acts = []
    for s in SHOTS:
        if not acts or acts[-1][0] != s["act"]:
            acts.append([s["act"], s["id"], s["tin"]])
    for i, a in enumerate(acts):
        end = acts[i + 1][2] if i + 1 < len(acts) else TOTAL
        o.append("| %s | %s… | %s – %s |" % (a[0], a[1], tc(a[2]), tc(end)))
    o += ["", "Mốc quan trọng:", "",
          "- VNISES xuất hiện lần đầu trên màn hình: **%s** (SH17); VO nhắc tên VNISES lần đầu: **%s**." % (tc(SBY["SH17"]["tin"]), tc(LBY["L16"]["start"])),
          "- Footage sản phẩm VNISES bắt đầu đúng câu “Ở đây, khoa học không chỉ được đọc.”: **%s** (SH18)." % tc(SBY["SH18"]["tin"]),
          "- Khoảng lặng 2 s sau “con người chưa bao giờ đứng ngoài tự nhiên”: **%s – %s**." % (tc(LBY["L10"]["end"]), tc(LBY["L10"]["end"] + LBY["L10"]["gap"])),
          "- Im lặng 1 s trước logo: **%s – %s**." % (tc(SBY["SH28"]["tin"]), tc(SBY["SH28"]["tin"] + 1.0)), "",
          "## Kịch bản (hai cột)", "",
          "| TC | Shot | Hình ảnh | Voice-over |", "|---|---|---|---|"]
    for s in SHOTS:
        vo = " ".join("“%s”" % l["vi"] for l in s["vo_lines"])
        o.append("| %s | %s | %s | %s |" % (tc(s["tin"]), s["id"], s["visual"].replace("|", "/"), vo))
    o += ["", "## Thay đổi so với VO baseline (biên tập nhẹ, không đổi tư tưởng, không thêm claim)", ""]
    for l in LINES:
        if l["note"] and ("Rút gọn" in l["note"] or "Gộp" in l["note"] or "Thêm" in l["note"]):
            o.append("- **%s** — %s → “%s”" % (l["id"], l["note"], l["vi"]))
    o += ["", "Lý do chung: baseline đọc với nhịp điềm tĩnh dài ~3:20 và đưa VNISES tới ~1:27. Các chỉnh sửa trên bỏ ~18 âm tiết lặp và gộp cặp câu đối xứng ở L34; kết hợp hiệu chỉnh khoảng lặng, phim còn %s." % tc(TOTAL), "",
          "## Change Requests cho Khang / ChatGPT", "",
          "**CR-01 — Thời điểm xuất hiện VNISES.** Brief yêu cầu khoảng 40–70 s; bản này đặt VNISES ở %s (lệch ~5 s). Nguyên nhân: phần VO baseline trước câu L16 dài ~240 âm tiết; đọc nhanh hơn sẽ phá yêu cầu “tempo chậm vừa, cho phép khoảng lặng”. Phương án nếu bắt buộc ≤ 70 s: bỏ câu L04 (“Vật chất, không gian và thời gian…”) và rút L07 còn ba vế (bỏ “đọc được thông tin chứa trong sự sống”) → VNISES ở ~1:07. Đây là thay đổi nội dung nên cần Owner quyết định; bản hiện tại giữ đủ ý baseline." % tc(SBY["SH17"]["tin"]),
          "",
          "**CR-02 — Footage sản phẩm.** Footage VNISES trong offline edit quay từ module giới thiệu `vnises-gioithieu.php` (đã xây dựng, chưa xác nhận đang chạy trên vnises.com). Master chỉ được phát hành sau khi module này hoặc tính năng tương đương đã lên vnises.com và được quay lại từ site thật (xem 16_VNISES_Footage_Capture_List.md).",
          "",
          "**CR-03 — Cách đọc tên VNISES.** Chưa có quy định chính thức. Đề xuất đọc liền ‘Vi-ni-xít’; cần Owner xác nhận trước khi thu VO.",
          ""]
    return "\n".join(o)

# ---------------------------------------------------------------------------
# 02 Storyboard
# ---------------------------------------------------------------------------
def storyboard():
    o = ["# Timecoded Storyboard · %s" % D.VERSION, "",
         "Mỗi shot đầy đủ 13 trường theo brief. Layer: **A** = ảnh/dữ liệu khoa học thật · **B** = footage sản phẩm VNISES · **C** = motion graphics dựng trong gói.", "",
         "Khung tham chiếu của từng shot: xem `renders/` (offline edit) — shot layer A hiển thị slate có ghi nguồn cho tới khi ảnh thật được chèn.", ""]
    for s in SHOTS:
        tname, tdur = trans(s)
        vo = " / ".join("%s “%s”" % (l["id"], l["vi"]) for l in s["vo_lines"]) or "—"
        o += ["## %s · %s" % (s["id"], s["act"]), "",
              "| Trường | Nội dung |", "|---|---|",
              "| SHOT ID | %s |" % s["id"],
              "| TIME IN | %s |" % tc(s["tin"]),
              "| TIME OUT | %s |" % tc(s["tout"]),
              "| DURATION | %.2f s (%d khung) |" % (s["dur"], round(s["dur"] * D.FPS)),
              "| VOICE-OVER | %s |" % vo,
              "| VISUAL | %s |" % s["visual"],
              "| SOURCE | Layer %s · %s |" % (s["layer"], ", ".join("%s (%s)" % (a, ABY[a]["org"]) for a in s["assets"])),
              "| MOTION | %s |" % s["motion"],
              "| TRANSITION | %s%s (vào shot) |" % (tname, (" %.1f s" % tdur) if tdur else ""),
              "| ON-SCREEN TEXT | %s |" % s["onscreen"],
              "| AUDIO | %s (cue: %s) |" % (s["audio"], ", ".join(CUE_BY_SHOT.get(s["id"], ["—"]))),
              "| SCIENTIFIC NOTE | %s |" % s["sci"],
              "| LICENSING NOTE | %s |" % s["lic"], ""]
    return "\n".join(o)

# ---------------------------------------------------------------------------
# CSVs
# ---------------------------------------------------------------------------
def to_csv(rows, header):
    buf = io.StringIO()
    w = csv.writer(buf, quoting=csv.QUOTE_MINIMAL, lineterminator="\n")
    w.writerow(header)
    for r in rows:
        w.writerow(r)
    return "﻿" + buf.getvalue()  # BOM để Excel mở đúng tiếng Việt

def shot_list():
    rows = []
    for s in SHOTS:
        tname, tdur = trans(s)
        rows.append([s["id"], tc(s["tin"]), tc(s["tout"]), "%.2f" % s["dur"], s["act"], s["layer"],
                     " / ".join(l["vi"] for l in s["vo_lines"]), s["visual"], "; ".join(s["assets"]), s["motion"],
                     "%s %.1fs" % (tname, tdur) if tdur else tname, s["onscreen"], s["audio"],
                     ", ".join(CUE_BY_SHOT.get(s["id"], [])), s["sci"], s["lic"]])
    return to_csv(rows, ["SHOT ID", "TIME IN", "TIME OUT", "DURATION (s)", "ACT", "LAYER", "VOICE-OVER", "VISUAL", "ASSETS",
                         "MOTION", "TRANSITION IN", "ON-SCREEN TEXT", "AUDIO", "SOUND CUES", "SCIENTIFIC NOTE", "LICENSING NOTE"])

def asset_register():
    used = {}
    for s in SHOTS:
        for a in s["assets"]:
            used.setdefault(a, []).append(s["id"])
    rows = []
    for a in D.ASSETS:
        rows.append([a["id"], a["layer"], a["desc"], a["source"], a["org"], a["url"], a["license"], a["credit"],
                     a["status"], "; ".join(used.get(a["id"], [])), a["note"]])
    return to_csv(rows, ["Asset ID", "Layer", "Description", "Source", "Author/Organization", "URL", "License",
                         "Credit requirement", "Usage status", "Used in shots", "Notes"])

SRC_CLIP = {
    "SH18": "renders/clips/B01.mp4", "SH19": "renders/clips/B02.mp4", "SH21": "renders/clips/B03.mp4",
    "SH22": "renders/clips/B04.mp4",
    "SH24": "renders/_comp/SH24.mp4 (= clips/SH24.mp4 → dissolve 0,6 s → clips/B05.mp4)",
    "SH26": "renders/_comp/SH26.mp4 (= clips/B06a.mp4 → dissolve 0,8 s → clips/B06b.mp4)",
}

def edl():
    rows = []
    for i, s in enumerate(SHOTS, 1):
        tname, tdur = trans(s)
        # Nguồn trong clip render: clip có handle 0,8 s ở đầu → source in = 00:00:00:20
        src_in = 0.8 - tdur / 2 if tname not in ("Cut", "Fade from black") else 0.8
        src_out = 0.8 + s["dur"]
        typo = TYPO[s["layer"]]
        if s["id"] in ("SH17", "SH28"):
            typo = "T1 wordmark + T2 tên đầy đủ" + (" + T7 URL + T5 credit" if s["id"] == "SH28" else "")
        rows.append([i, s["id"], s["layer"], tc(s["tin"]), tc(s["tout"]), round(s["dur"] * D.FPS),
                     "; ".join(s["assets"]), SRC_CLIP.get(s["id"], "renders/clips/%s.mp4" % s["id"]),
                     tc(max(0, src_in)), tc(src_out), tname, "%.2f" % tdur,
                     "; ".join(l["id"] for l in s["vo_lines"]),
                     " / ".join(l["vi"] for l in s["vo_lines"]), s["onscreen"], typo,
                     ", ".join(CUE_BY_SHOT.get(s["id"], [])), s["motion"]])
    return to_csv(rows, ["EVENT", "SHOT ID", "LAYER", "RECORD IN", "RECORD OUT", "DURATION (frames)", "ASSET IDS", "SOURCE CLIP",
                         "SOURCE IN", "SOURCE OUT", "TRANSITION IN", "TRANSITION DUR (s)", "VO IDS", "VO TEXT", "ON-SCREEN TEXT",
                         "TYPOGRAPHY", "SOUND CUE", "MOTION / NOTES"])

# ---------------------------------------------------------------------------
# 08 Music & sound
# ---------------------------------------------------------------------------
def music_doc():
    o = ["# Music & Sound Design Guide · %s" % D.VERSION, "",
         "## Nguyên tắc", "",
         "- Nhạc **đứng sau lời đọc**: ambient cinematic tối giản, phát triển chậm. Không epic orchestra, không trailer percussion, không boom/riser, không corporate uplifting.",
         "- Hòa âm: quãng năm và quãng hai trên nền A (A–E–B, thêm F# ở bè cao). Tránh hợp âm trưởng đầy đủ (cảm giác “uplifting”).",
         "- Sound design rất tiết chế: room tone, low-frequency texture, tối đa một “data tone”. **Không** laser, whoosh liên tục, beep sci-fi, tiếng động cơ tàu vũ trụ, âm thanh HUD, tiếng click chuột giả.",
         "- Đường cong: rất nhẹ ở đầu → mở rộng một chút ở lộ diện VNISES (SH17) → rút lại ở Scientific Integrity (SH25) → tối giản ở kết.", "",
         "## Cue sheet", "",
         "| Cue | Shot | Timecode | Mô tả |", "|---|---|---|---|"]
    for cid, sid, desc in CUES:
        t = SBY[sid]["tin"]
        if cid == "M2":
            t = LBY["L06"]["start"]
        if cid == "M5":
            t = LBY["L10"]["end"]
        if cid == "M11":
            t = SBY["SH28"]["tin"]
        o.append("| %s | %s | %s | %s |" % (cid, sid, tc(t), desc))
    o += ["", "## Mức âm (web master)", "",
          "| Thành phần | Mục tiêu |", "|---|---|",
          "| Integrated loudness toàn phim | −16 LUFS (±1), True Peak ≤ −1 dBTP |",
          "| VO | đỉnh lời đọc ~ −18 đến −16 LUFS short-term |",
          "| Nhạc dưới VO | thấp hơn VO 18–22 dB |",
          "| Nhạc ở khoảng lặng | được nâng 3–6 dB, không vượt mức VO trung bình |",
          "| Bản broadcast (nếu cần) | −23 LUFS (EBU R128) |", "",
          "## Nguồn nhạc", "",
          "- **Ưu tiên:** nhạc sáng tác riêng (work-for-hire, VNISES sở hữu bản quyền).",
          "- **Phương án 2:** thư viện có licence rõ cho sử dụng trên web/mạng xã hội/sự kiện, lưu chứng từ licence cùng gói.",
          "- **Không** dùng nhạc trên YouTube Audio Library hay nhạc “no copyright” không có chứng từ.",
          "- Brief cho composer: tempo tự do (rubato) hoặc ~60 BPM không có phách rõ; pad + piano chuẩn bị (prepared/felt piano) hoặc đàn dây chơi sul tasto rất nhẹ; một chuyển động hòa âm duy nhất ở SH17.", "",
          "## Temp bed trong offline edit", "",
          "`renders/` dùng một temp bed tổng hợp tất định (`src/render/audio_temp.py`) theo đúng cue sheet trên để editor cảm nhịp. **Không dùng cho master.**", ""]
    return "\n".join(o)

STATIC = {}

STATIC["09_Motion_Graphics_Spec.md"] = """# Motion Graphics Spec · {ver}

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
"""

STATIC["10_Color_Typography_Spec.md"] = """# Color & Typography Spec · {ver}

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
"""

STATIC["12_Render_Settings.md"] = """# Render Settings · {ver}

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
ffmpeg -i master_prores.mov -c:v libx264 -profile:v high -level 4.2 -pix_fmt yuv420p \\
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
"""

STATIC["16_VNISES_Footage_Capture_List.md"] = """# VNISES Footage Capture List · {ver}

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
"""

def sci_audit():
    o = ["# Scientific Audit · %s" % D.VERSION, "",
         "Phân loại: **FACT** (đã xác lập) · **OBSERVATION** · **DATA** · **SIMULATION** · **ASSUMPTION** · **INFERENCE** · **CONCEPTUAL**.", "",
         "## Kiểm tra lời đọc", "",
         "| Câu | Nội dung khoa học | Phân loại | Kết luận |", "|---|---|---|---|",
         "| L07c | Ánh sáng đi qua vũ trụ hàng tỷ năm | FACT | Đúng: ánh sáng từ các thiên hà xa nhất trong HUDF/Webb Deep Field đã đi hơn 13 tỷ năm. |",
         "| L07a | Nhìn sâu vào cấu trúc vật chất | FACT | Đúng; hình minh họa là ảnh STM (dữ liệu dựng thành ảnh), chú thích nói rõ. |",
         "| L07b | Đọc thông tin chứa trong sự sống | FACT | Đúng (giải trình tự DNA); hình là chromatogram — dữ liệu đo. |",
         "| L11 | Vật chất tạo nên chúng ta là vật chất của vũ trụ | FACT | Câu trung tính theo brief. Không nói “mọi nguyên tử từ sao”. MG SH13 phân biệt: H chủ yếu hình thành trong vũ trụ sơ khai; C, N, O, P, Ca chủ yếu trong sao và vụ nổ sao. |",
         "| L12 | Quy luật ta tìm hiểu là quy luật của thế giới ta sống | FACT (nguyên lý tính phổ quát của định luật vật lý) | Minh họa bằng khẩu pháo Newton: cùng một định luật cho vật rơi và quỹ đạo. |",
         "| L14 | Hiểu biết được sửa lại | FACT | Ví dụ trên màn hình: Hubble (1929) ước tính hằng số giãn nở ~500 km/s/Mpc; giá trị hiện nay ~67–73 km/s/Mpc → lớn gấp khoảng 7 lần. |",
         "| L24 | Ánh sáng → lượng tử → thiên văn học | FACT | Phổ vạch (lượng tử) là nền tảng của quang phổ thiên văn. |",
         "| L25 | Chuyển động → quỹ đạo → du hành không gian | FACT | Cơ học Newton → cơ học thiên thể → động lực học bay vũ trụ. |",
         "| L26 | Quan sát thiên hà → lịch sử vũ trụ | FACT | Dịch chuyển đỏ của thiên hà là bằng chứng cho vũ trụ giãn nở (vũ trụ học). |",
         "| L36 | Có thể luôn còn câu hỏi vượt quá hiểu biết hiện tại | INFERENCE (được nói đúng là khả năng: “có thể”) | Giữ nguyên. |", "",
         "## Kiểm tra hình ảnh", "",
         "| Shot | Layer | Phân loại | Rủi ro & xử lý |", "|---|---|---|---|"]
    for s in SHOTS:
        o.append("| %s | %s | %s | %s |" % (s["id"], s["layer"], {"A": "OBSERVATION/DATA (ảnh thật)", "B": "UI sản phẩm (nội dung SIMULATION có nhãn)", "C": "SIMULATION / FACT / CONCEPTUAL (có nhãn)"}[s["layer"]], s["sci"]))
    o += ["", "## Những điều đã loại bỏ", "",
          "- Không có telemetry, vị trí ISS, dữ liệu Mặt Trời, timestamp “live”, số liệu quỹ đạo thật hay biểu đồ khoa học giả trong bất kỳ khung hình nào.",
          "- Không có ảnh do AI tạo; không ảnh minh họa tàu vũ trụ trình bày như ảnh chụp (A09 phải là ảnh chụp phòng sạch).",
          "- Không starfield, nebula nền, hạt bay, DNA phát sáng, hologram, phương trình bay lơ lửng.",
          "- Sơ đồ SH25 không có trục số; SH20 và SH14 ghi rõ SIMULATION; SH14 ghi rõ độ cao núi được phóng đại.",
          "- Hình CMB không được gọi là “ảnh Big Bang”; stromatolite hiện đại không được gọi là “sự sống đầu tiên”.", "",
          "## Verification các mô phỏng", "",
          "- SH14: RK4, dt = 0,0025 (đơn vị chuẩn hóa); kiểm tra: với v = v_tròn, bán kính giữ 1,000 ± 0,001 sau một vòng; với v < v_tròn, điểm phóng là viễn điểm (đúng lý thuyết).",
          "- SH20: Euler với dt = 0,002; kiểm tra định tính: có lực cản ∝ v² → tầm xa giảm, đỉnh thấp hơn và lệch về phía điểm phóng, nhánh rơi dốc hơn.",
          "- B02/B03: footage lab Kepler — tỉ số b/a, r_cận/r_viễn, v_cận/v_viễn được tính trực tiếp từ e (đã kiểm thử trong module: e = 0,85 → v_cận/v_viễn = 12,33).",
          "- **Validation:** các mô phỏng là minh họa định tính/giáo dục; không có validation thực nghiệm và không cần cho mục đích minh họa. Không trình bày chúng như dữ liệu đo.", ""]
    return "\n".join(o)

def copy_audit():
    o = ["# Copyright & Provenance Audit · %s" % D.VERSION, "",
         "## Tóm tắt", "",
         "| Nhóm | Số asset | Trạng thái |", "|---|---|---|"]
    by = {}
    for a in D.ASSETS:
        by.setdefault(a["layer"], []).append(a)
    o.append("| A — ảnh/dữ liệu khoa học | %d | Chưa tải. Môi trường sản xuất bị chặn mạng tới mọi nguồn → **mọi URL và licence phải xác minh khi tải**. 1 asset UNKNOWN (A03) có phương án thay thế. |" % len(by["A"]))
    o.append("| B — footage VNISES | %d | Thuộc VNISES. Đã quay từ module giới thiệu; cần quay lại từ vnises.com (CR-02). |" % len(by["B"]))
    o.append("| C — motion graphics | %d | Dựng trong gói, thuộc VNISES. Font SIL OFL 1.1 (cho phép nhúng trong video). |" % len(by["C"]))
    o += ["", "## Quy tắc áp dụng", "",
          "- **NASA / JPL:** phần lớn hình ảnh không có bản quyền tại Hoa Kỳ; phải ghi credit, không dùng logo/insignia NASA, không ngụ ý NASA bảo trợ VNISES. Ảnh có người nhận diện được (A09) cần kiểm tra quyền hình ảnh cá nhân nếu dùng cho mục đích quảng bá.",
          "- **ESA/Hubble, ESA/Webb, ESO:** CC BY 4.0 — bắt buộc credit đúng như trang ảnh. Credit hiển thị trên màn hình (T5) và trong thẻ credit cuối phim.",
          "- **ESA (Planck):** kiểm tra từng ảnh — ESA Standard Licence hoặc CC BY-SA 3.0 IGO. Nếu CC BY-SA: video không trở thành tác phẩm phái sinh của bản đồ (dùng nguyên vẹn), nhưng phải ghi credit và licence.",
          "- **CERN:** phần lớn media theo CC BY 4.0; một số ảnh giữ “© CERN” với điều khoản riêng — kiểm tra bản ghi CDS.",
          "- **Tư liệu lịch sử (A13–A16):** tác phẩm gốc thuộc public domain. Bản scan có thể kèm điều khoản của thư viện — chỉ dùng scan không có điều kiện NC.",
          "- **A16 (Hubble 1929):** public domain tại Hoa Kỳ từ 01/01/2025; tác giả mất 1953 nên hết hạn ở các nước theo thời hạn đời tác giả + 70 năm (và + 50 năm). Nếu phát hành thương mại ở thị trường có quy định khác, xin ý kiến tư vấn pháp lý.",
          "- **Không** lấy ảnh từ Google Images, Pinterest, wallpaper site, hay các bản re-upload không rõ nguồn.", "",
          "## Bảng kiểm từng asset", "",
          "| Asset | Licence | Credit | Trạng thái | Hành động |", "|---|---|---|---|---|"]
    for a in D.ASSETS:
        act = "Tải từ URL nguồn, lưu trang licence (PDF) và metadata cùng file" if a["layer"] == "A" else ("Quay lại từ vnises.com" if a["layer"] == "B" else "—")
        if "UNKNOWN" in a["status"]:
            act = "Chỉ dùng nếu tìm được nguồn PD/CC0/CC BY; nếu không, dùng phương án thay thế ghi trong 04_Asset_Register"
        o.append("| %s | %s | %s | %s | %s |" % (a["id"], a["license"], a["credit"], a["status"], act))
    o += ["", "## Thẻ credit cuối phim (đã dựng trong SH28)", "",
          "Hình ảnh: ESO · ESA and the Planck Collaboration · CERN · NASA, ESA, S. Beckwith (STScI) and the HUDF Team · NIST · NHGRI · ESA/Hubble & NASA, RELICS · NASA, ESA, CSA, STScI · NASA · NASA / Apollo 8 — William Anders · NASA/JPL-Caltech · Tư liệu lịch sử: Galileo (1610), Kepler (1609), Newton (1687), Hubble (1929) · Mô phỏng và đồ họa: VNISES", "",
          "Sau khi chọn đúng file cho các asset TO SELECT, cập nhật thẻ credit với tên tác giả và thư viện cụ thể (ví dụ “ESO/Y. Beletsky”, “Library of Congress”).", ""]
    return "\n".join(o)

def readme(srt_vi_n, srt_en_n):
    o = ["# VNISES — Manifesto Film · Production Package %s" % D.VERSION, "",
         "Phim tuyên ngôn chính thức của **VNISES — Vietnam Nexus for Interactive Space Exploration and Science**.",
         "Thời lượng **%s** · 16:9 · 25 fps · %d shot." % (tc(TOTAL), len(SHOTS)), "",
         "## Trạng thái giao", "",
         "| Hạng mục | Trạng thái |", "|---|---|",
         "| Kịch bản, VO, storyboard, shot list, EDL, phụ đề VI/EN, hướng dẫn âm thanh, motion, màu/typography, render, audit | **Hoàn chỉnh** (thư mục này) |",
         "| Motion graphics (layer C, 9 shot) | **Đã render** — chất lượng final, dựng bằng code, tái lập được |",
         "| Footage VNISES (layer B) | **Đã quay** từ module giới thiệu VNISES; cần quay lại từ vnises.com trước master (CR-02) |",
         "| Ảnh/dữ liệu khoa học thật (layer A, 16 asset) | **Chưa tải** — môi trường sản xuất bị chặn mạng tới NASA/ESA/ESO/CERN; offline edit dùng slate ghi nguồn |",
         "| Voice-over | **Chưa thu** — không có giọng đọc tổng hợp đạt yêu cầu “không robot”; script thu âm đầy đủ trong 05 |",
         "| Nhạc | **Temp bed** tổng hợp theo cue sheet; master cần nhạc sáng tác/cấp phép |",
         "| Video master 01–04 | **Chưa xuất** — phụ thuộc layer A + VO + nhạc. Đã xuất **offline edit** đúng timing master |", "",
         "## Danh mục", "",
         "| File | Nội dung |", "|---|---|",
         "| 01_Master_Script.md | Kịch bản hai cột, mốc thời gian, chỉnh sửa VO, Change Requests |",
         "| 02_Timecoded_Storyboard.md | 13 trường cho từng shot |",
         "| 03_Shot_List.csv | Shot list (mở được bằng Excel/Sheets) |",
         "| 04_Asset_Register.csv | Đăng ký asset: nguồn, URL, licence, credit, trạng thái |",
         "| 05_Voiceover_Final.txt | Lời đọc cuối, ngắt nghỉ, nhấn, phát âm, timing, hướng dẫn thu |",
         "| 06_VNISES_manifesto_vi.srt | Phụ đề tiếng Việt (%d event) |" % srt_vi_n,
         "| 07_VNISES_manifesto_en.srt | Phụ đề tiếng Anh (%d event) |" % srt_en_n,
         "| 08_Music_Sound_Design_Guide.md | Nguyên tắc, cue sheet có timecode, mức âm |",
         "| 09_Motion_Graphics_Spec.md | Thông số từng shot MG |",
         "| 10_Color_Typography_Spec.md | Bảng màu, font, style chữ |",
         "| 11_Edit_Decision_List.csv | EDL: record/source TC, transition, VO, asset, typography, sound cue |",
         "| 12_Render_Settings.md | Thông số xuất master + cách tái lập offline edit |",
         "| 13_Scientific_Audit.md | Kiểm tra khoa học lời đọc và hình |",
         "| 14_Copyright_Audit.md | Kiểm tra bản quyền/provenance, thẻ credit |",
         "| 16_VNISES_Footage_Capture_List.md | Danh sách quay màn hình vnises.com |",
         "| 17_Danh_sach_anh_va_nguon_tai.docx | Danh sách 16 ảnh khoa học và nguồn tải (Word), sinh bằng src/build_asset_docx.js |", "",
         "Không có file 15: gói **không dùng hình ảnh AI-generated** — mọi hình minh họa khái niệm là motion graphics dựng bằng code, tất định, có nhãn loại thông tin.", "",
         "## Self-audit (sau 2 vòng tự sửa)", "",
         "| Gate | Kết quả | Ghi chú |", "|---|---|---|",
         "| G1 Narrative | PASS* | Đúng hành trình Câu hỏi → Khám phá → Con người là một phần vũ trụ → Tri thức qua thế hệ → VNISES → Mô hình/dữ liệu/tương tác → Nexus → Kiểm chứng → Chưa biết → VNISES. *VNISES xuất hiện ở %s, lệch ~5 s so với khung 40–70 s — ghi thành CR-01 kèm phương án cắt để Owner quyết. |" % tc(SBY["SH17"]["tin"]),
         "| G2 Scientific accuracy | PASS | Không có claim “mọi nguyên tử từ sao”; CMB, stromatolite, STM, Hubble 1929 được chú thích đúng; SH14 và SH20 là tích phân số thật, có nhãn SIMULATION (13_Scientific_Audit). |",
         "| G3 VNISES identity | PASS | Lộ diện tiết chế (không flare/scale); footage UI thật; token màu và nhãn loại thông tin đồng bộ với website. |",
         "| G4 Visual quality | PASS (layer B, C) | Đã soát từng khung MG và footage; sửa nhãn đè, khung lộ sơ đồ, phụ đề chồng UI. Layer A sẽ được đánh giá lại sau khi chèn ảnh thật. |",
         "| G5 Authenticity | PASS | Không AI image, không telemetry/timestamp/số liệu giả; sơ đồ khái niệm không có trục số; footage chỉ có crop/zoom/matte/scrim. |",
         "| G6 Asset provenance | PASS | 31 asset trong register; trạng thái trung thực (VERIFY / TO SELECT / UNKNOWN có phương án thay thế). |",
         "| G7 Copyright | PASS (có điều kiện) | Chưa asset bên ngoài nào được dùng khi chưa rõ licence; mọi asset A phải xác minh khi tải; thẻ credit CC BY đã dựng. |",
         "| G8 Voice-over | PASS (script) | Lời đọc có ngắt nghỉ, nhấn, phát âm, timing, hướng dẫn thu. Bản thu chưa có — không dùng TTS robot. |",
         "| G9 Editing rhythm | PASS | 28 shot, trung bình 6,3 s; shot ngắn nhất 2,5 s (câu hỏi mở đầu); khoảng lặng 2 s sau L10, 1 s trước logo; transition chủ yếu cut/dissolve, dip-to-black chỉ ở chuyển hồi. |",
         "| G10 Technical delivery | PASS (offline edit) | Offline edit 1080p25 đúng 100%% thời lượng timeline; SRT VI/EN ≤ 42 ký tự/dòng, ≤ 2 dòng, ≤ 18,6 ký tự/giây; CSV UTF-8 BOM. Master 01–04 chờ ảnh thật + VO + nhạc. |", "",
         "## Review chống “AI cliché” — đã phát hiện và xử lý", "",
         "- Cặp câu đối xứng “…không để làm đơn giản hơn. Cũng không để làm phức tạp hơn.” → gộp thành một câu (L34).",
         "- Chuỗi năm câu song song L18–L21 giữ theo baseline, nhưng mỗi câu có một hình chứng minh riêng (footage thật/mô phỏng) và khoảng lặng khác nhau để tránh nhịp máy móc.",
         "- Loại khỏi thiết kế: starfield, nebula nền, hạt bay, DNA phát sáng, hologram, phương trình bay, phi hành gia silhouette, “nhà khoa học nhìn màn hình”.",
         "- Lộ diện VNISES không dùng lens flare/scale/riser; nhạc chỉ mở rộng một lần.",
         "- Không có câu tự ca ngợi; VNISES được chứng minh bằng cách trình bày (footage lab, provenance, nhãn loại thông tin).", ""]
    return "\n".join(o)

if __name__ == "__main__":
    import timeline
    timeline.export_json(os.path.join(HERE, "render", "timeline.json"))
    vi, ev_vi = srt("vi")
    en, ev_en = srt("en")
    write("01_Master_Script.md", master_script())
    write("02_Timecoded_Storyboard.md", storyboard())
    write("03_Shot_List.csv", shot_list())
    write("04_Asset_Register.csv", asset_register())
    write("05_Voiceover_Final.txt", vo_txt())
    write("06_VNISES_manifesto_vi.srt", vi)
    write("07_VNISES_manifesto_en.srt", en)
    write("08_Music_Sound_Design_Guide.md", music_doc())
    for name, body in STATIC.items():
        write(name, body.replace("{ver}", D.VERSION))
    write("11_Edit_Decision_List.csv", edl())
    write("13_Scientific_Audit.md", sci_audit())
    write("14_Copyright_Audit.md", copy_audit())
    write("README.md", readme(len(ev_vi), len(ev_en)))
    print("package written:", OUT, "| total", tc(TOTAL), "| shots", len(SHOTS), "| srt vi/en", len(ev_vi), len(ev_en))
