# -*- coding: utf-8 -*-
"""Tính timeline (VO + shot) từ film_data. Mọi thời điểm làm tròn theo khung hình."""
import re
import film_data as D

def syllables(text):
    return len(re.findall(r"[0-9A-Za-zÀ-ỹ]+", text))

def frames(t):
    return int(round(t * D.FPS))

def tc(t):
    f = frames(t)
    s, fr = divmod(f, D.FPS)
    m, s = divmod(s, 60)
    h, m = divmod(m, 60)
    return "%02d:%02d:%02d:%02d" % (h, m, s, fr)

def build():
    t = D.LEAD_IN
    lines = []
    for (lid, vi, en, rate, gap, note) in D.VO:
        syl = D.EN_SYLLABLE_OVERRIDE.get(lid, syllables(vi))
        dur = syl / rate + 0.22 * vi.count(",") + 0.30 * vi.count("—")
        start = frames(t) / D.FPS
        end = frames(t + dur) / D.FPS
        lines.append(dict(id=lid, vi=vi, en=en, rate=rate, gap=gap, note=note, syl=syl,
                          start=start, end=end, dur=end - start))
        t = end + gap
    total = frames(lines[-1]["end"] + lines[-1]["gap"] + D.TAIL) / D.FPS
    by_id = {l["id"]: l for l in lines}
    shots = []
    for i, s in enumerate(D.SHOTS):
        first = by_id[s["vo"][0]]
        if s["id"] == "SH01":
            tin = 0.0
        else:
            lead = D.SHOT_LEAD.get(s["id"]) or 0.4
            tin = frames(first["start"] - lead) / D.FPS
        shots.append(dict(s, tin=tin))
    for i, s in enumerate(shots):
        s["tout"] = shots[i + 1]["tin"] if i + 1 < len(shots) else total
        s["dur"] = s["tout"] - s["tin"]
        s["vo_lines"] = [by_id[x] for x in s["vo"]]
    return lines, shots, total

if __name__ == "__main__":
    lines, shots, total = build()
    for s in shots:
        print(s["id"], tc(s["tin"]), tc(s["tout"]), "%5.2fs" % s["dur"], s["layer"], ",".join(s["vo"]))
    print("TOTAL", tc(total), "%.1fs" % total)
    print("VNISES on screen (SH17 in):", tc([s for s in shots if s["id"] == "SH17"][0]["tin"]))
    print("L16 start:", tc([l for l in lines if l["id"] == "L16"][0]["start"]))

def export_json(path):
    import json
    lines, shots, total = build()
    out = dict(fps=D.FPS, total=total, lines=lines,
               shots=[dict(id=s["id"], tin=s["tin"], tout=s["tout"], dur=s["dur"], layer=s["layer"],
                           trans_in=s["trans_in"], assets=s["assets"],
                           visual=s["visual"], motion=s["motion"], onscreen=s["onscreen"],
                           asset_info=[a for a in D.ASSETS if a["id"] in s["assets"]],
                           cues=[dict(id=l["id"], t=round(l["start"] - s["tin"], 3), end=round(l["end"] - s["tin"], 3)) for l in s["vo_lines"]])
                      for s in shots])
    with open(path, "w", encoding="utf-8") as f:
        json.dump(out, f, ensure_ascii=False, indent=1)
    return out
