/* VNISES manifesto — quay footage sản phẩm (layer B) từ module giới thiệu thật.
 *
 * Mỗi clip: trang vnises_page.html (sinh từ vnises-gioithieu.php), đồng hồ trình duyệt được
 * điều khiển (Playwright clock) nên chuyển động quỹ đạo tất định ở 25 fps.
 * Camera = translate + scale trên <body> (không chỉnh sửa nội dung UI).
 * Thao tác (kéo thanh trượt, bấm nút, chọn khái niệm) gọi đúng event handler của module.
 *
 * Dùng: node capture.js <outDir> <dpr> [clipId...]
 */
'use strict';
const { chromium } = require('playwright');
const { spawn } = require('child_process');
const path = require('path');
const fs = require('fs');

const FPS = 25;
const PRE = 0.8;   // handle trước (giây)
const POST = 0.8;  // handle sau (giây)
const PAGE = 'file://' + path.join(__dirname, 'vnises_page.html');
const TL = JSON.parse(fs.readFileSync(path.join(__dirname, 'timeline.json'), 'utf8'));
const durOf = id => TL.shots.find(s => s.id === id).dur;

const ease = x => (x < 0.5 ? 4 * x * x * x : 1 - Math.pow(-2 * x + 2, 3) / 2);
const ramp = (t, a, b) => Math.max(0, Math.min(1, (t - a) / (b - a)));
const lerp = (a, b, k) => a + (b - a) * k;

/* Định nghĩa clip: dur, setup(page) → doc refs, frame(page, t, refs) → {cam:{x,y,z}, cursor:{x,y,down}|null} */
const CLIPS = {
	B01: { // SH18 — từ tiêu đề trang tới hình quỹ đạo Kepler đang chuyển động
		dur: () => durOf('SH18'),
		setup: async p => p.evaluate(() => {
			const r = e => { const b = document.querySelector(e).getBoundingClientRect(); return { x: b.left + b.width / 2, y: b.top + b.height / 2, w: b.width, h: b.height }; };
			return { title: r('.vngt-title--opening'), orbit: r('[data-vngt-hero-orbit]') };
		}),
		frame: (t, R) => {
			const k = ease(ramp(t, 1.0, 3.4)), z = lerp(1.0, 1.0, k) * lerp(1, 1.55, ease(ramp(t, 3.2, 5.8)));
			return { cam: { x: lerp(960, R.orbit.x, k), y: lerp(540, R.orbit.y + 30, k), z: z } };
		}
	},
	B02: { // SH19 — kéo e từ 0,10 lên 0,70
		dur: () => durOf('SH19'),
		setup: async p => {
			await p.evaluate(() => { const i = document.querySelector('[data-vngt-root] [data-vngt-range]'); i.value = '0.1'; i.dispatchEvent(new Event('input', { bubbles: true })); });
			return p.evaluate(() => {
				const b = document.querySelector('[data-vngt-root] .vngt-lab').getBoundingClientRect();
				const i = document.querySelector('[data-vngt-root] [data-vngt-range]').getBoundingClientRect();
				return { lab: { x: b.left + b.width / 2, y: b.top + 400 }, rng: { l: i.left + 13, r: i.right - 13, y: i.top + i.height / 2 } };
			});
		},
		action: async (p, t) => {
			const e = lerp(0.10, 0.70, ease(ramp(t, 0.35, 2.5)));
			await p.evaluate(v => { const i = document.querySelector('[data-vngt-root] [data-vngt-range]'); if (Math.abs(parseFloat(i.value) - v) > 0.004) { i.value = v.toFixed(2); i.dispatchEvent(new Event('input', { bubbles: true })); } }, e);
			return e;
		},
		frame: (t, R, e) => ({ cam: { x: R.lab.x, y: R.lab.y, z: 1.25 }, cursor: { x: lerp(R.rng.l, R.rng.r, e / 0.85), y: R.rng.y, down: t > 0.3 && t < 2.6 } })
	},
	B03: { // SH21 — ghim tham chiếu e = 0,30 rồi kéo lên 0,75
		dur: () => durOf('SH21'),
		setup: async p => {
			await p.evaluate(() => { const i = document.querySelector('[data-vngt-root] [data-vngt-range]'); i.value = '0.3'; i.dispatchEvent(new Event('input', { bubbles: true })); });
			return p.evaluate(() => {
				const b = document.querySelector('[data-vngt-root] .vngt-lab').getBoundingClientRect();
				const i = document.querySelector('[data-vngt-root] [data-vngt-range]').getBoundingClientRect();
				const pin = document.querySelector('[data-vngt-root] [data-vngt-pin]').getBoundingClientRect();
				return { lab: { x: b.left + b.width / 2, y: b.top + 400 }, rng: { l: i.left + 13, r: i.right - 13, y: i.top + i.height / 2 }, pin: { x: pin.left + pin.width / 2, y: pin.top + pin.height / 2 } };
			});
		},
		action: async (p, t, R, st) => {
			if (t >= 0.6 && !st.pinned) { st.pinned = true; await p.evaluate(() => document.querySelector('[data-vngt-root] [data-vngt-pin]').click()); }
			const e = lerp(0.30, 0.75, ease(ramp(t, 1.05, 2.6)));
			await p.evaluate(v => { const i = document.querySelector('[data-vngt-root] [data-vngt-range]'); if (Math.abs(parseFloat(i.value) - v) > 0.004) { i.value = v.toFixed(2); i.dispatchEvent(new Event('input', { bubbles: true })); } }, e);
			return e;
		},
		frame: (t, R, e) => {
			const thumb = { x: lerp(R.rng.l, R.rng.r, e / 0.85), y: R.rng.y };
			let c;
			if (t < 0.55) { const k = ease(ramp(t, -0.3, 0.55)); c = { x: lerp(thumb.x + 120, R.pin.x, k), y: lerp(thumb.y - 40, R.pin.y, k), down: false }; }
			else if (t < 0.75) { c = { x: R.pin.x, y: R.pin.y, down: t < 0.68 }; }
			else { const k = ease(ramp(t, 0.75, 1.0)); c = { x: lerp(R.pin.x, thumb.x, k), y: lerp(R.pin.y, thumb.y, k), down: t > 1.0 && t < 2.65 }; }
			return { cam: { x: R.lab.x, y: R.lab.y, z: 1.25 }, cursor: c };
		}
	},
	B04: { // SH22 — một câu hỏi, nhiều hướng đi
		dur: () => durOf('SH22'),
		setup: async p => p.evaluate(() => { const b = document.querySelector('[data-vngt-root] .vngt-question').getBoundingClientRect(); return { q: { x: b.left + b.width / 2, top: b.top, bottom: b.bottom } }; }),
		frame: (t, R) => ({ cam: { x: R.q.x + 40, y: lerp(R.q.top + 175, R.q.bottom - 190, ease(ramp(t, 0.2, 4.2))), z: 1.85 }, matte: 430 })
	},
	B05: { // SH24 (nửa sau) — sơ đồ Nexus thật, chọn 'Mô hình'
		dur: () => 3.2,
		setup: async p => p.evaluate(() => {
			const s = document.querySelector('[data-vngt-root] .vngt-nexus').getBoundingClientRect();
			const n = document.querySelectorAll('[data-vngt-root] [data-vngt-node]')[2].getBoundingClientRect();
			return { st: { x: s.left + s.width / 2, y: s.top + s.height / 2 }, node: { x: n.left + n.width / 2, y: n.top + n.height / 2 } };
		}),
		action: async (p, t, R, st) => { if (t >= 0.9 && !st.clicked) { st.clicked = true; await p.evaluate(() => document.querySelectorAll('[data-vngt-root] [data-vngt-node]')[2].click()); } },
		frame: (t, R) => { const k = ease(ramp(t, 0.0, 0.85)); return { cam: { x: R.st.x, y: R.st.y, z: 1.08 }, cursor: { x: lerp(R.node.x + 160, R.node.x + 6, k), y: lerp(R.node.y + 120, R.node.y + 8, k), down: t > 0.85 && t < 1.0 } }; }
	},
	B06a: { // SH26 (phần 1) — bảng provenance áp dụng cho mô hình quỹ đạo
		dur: () => 6.6,
		setup: async p => p.evaluate(() => { const b = document.querySelector('[data-vngt-root] .vngt-prov').getBoundingClientRect(); return { pv: { x: b.left + b.width / 2, top: b.top, bottom: b.bottom } }; }),
		frame: (t, R) => ({ cam: { x: R.pv.x, y: lerp(R.pv.top + 330, R.pv.bottom - 300, ease(ramp(t, 0.0, 6.6))), z: 1.2 } })
	},
	B06b: { // SH26 (phần 2) — chuỗi Quan sát → … → Hiểu
		dur: () => 6.6,
		setup: async p => p.evaluate(() => { const b = document.querySelector('[data-vngt-root] .vngt-steps').getBoundingClientRect(); return { s: { x: b.left + b.width / 2, y: b.top + b.height / 2 } }; }),
		frame: (t, R) => ({ cam: { x: R.s.x, y: R.s.y, z: lerp(1.25, 1.38, ease(ramp(t, 0, 6.6))) } })
	}
};

const CURSOR_SVG = '<svg xmlns="http://www.w3.org/2000/svg" width="26" height="34" viewBox="0 0 26 34"><path d="M2 2 L2 27 L8.5 21 L13 31.5 L17.2 29.7 L12.8 19.5 L21.5 19.5 Z" fill="#f4f3ee" stroke="#0b0c0d" stroke-width="2" stroke-linejoin="round"/></svg>';

async function renderClip(browser, id, outDir, dpr) {
	const spec = CLIPS[id];
	const dur = spec.dur() + PRE + POST;
	const n = Math.round(dur * FPS);
	const ctx = await browser.newContext({ viewport: { width: 1920, height: 1080 }, deviceScaleFactor: dpr });
	const page = await ctx.newPage();
	const errors = [];
	page.on('pageerror', e => errors.push(e.message));
	await page.clock.install({ time: new Date('2026-01-01T00:00:00Z') });
	await page.goto(PAGE);
	await page.addStyleTag({ content: [
		'html{overflow:hidden!important}',
		'body{transform-origin:0 0}',
		/* Footage chỉ bỏ hiệu ứng reveal để nội dung hiện đầy đủ; animation quỹ đạo giữ nguyên. */
		'.vngt-root.vngt-motion [data-vngt-reveal]{opacity:1!important;transform:none!important;transition:none!important}',
		'.vngt-root [data-vngt-draw]{stroke-dashoffset:0!important}',
		'#cap-matte{position:fixed;left:0;top:0;bottom:0;z-index:99998;pointer-events:none;background:linear-gradient(90deg,#0b0c0d 0,#0b0c0d 70%,rgba(11,12,13,0) 100%);display:none}',
		/* Scrim vùng phụ đề: gradient tối ở 25% dưới khung — lớp phủ khi quay, không thay đổi UI. */
		'#cap-scrim{position:fixed;left:0;right:0;bottom:0;height:270px;z-index:99997;pointer-events:none;background:linear-gradient(180deg,rgba(11,12,13,0) 0,rgba(11,12,13,.82) 62%,rgba(11,12,13,.9) 100%)}',
		'#cap-cursor{position:fixed;left:0;top:0;z-index:99999;pointer-events:none;transform-origin:2px 2px;display:none}'
	].join('\n') });
	await page.evaluate(svg => { const d = document.createElement('div'); d.id = 'cap-cursor'; d.innerHTML = svg; document.documentElement.appendChild(d); const m = document.createElement('div'); m.id = 'cap-matte'; document.documentElement.appendChild(m); const sc = document.createElement('div'); sc.id = 'cap-scrim'; document.documentElement.appendChild(sc); }, CURSOR_SVG);
	await page.clock.runFor(500);
	const R = await spec.setup(page);
	const st = {};
	const file = path.join(outDir, id + '.mp4');
	const ff = spawn('ffmpeg', ['-y', '-loglevel', 'error', '-f', 'image2pipe', '-framerate', String(FPS), '-i', '-',
		'-vf', `scale=${1920 * Math.min(dpr, 2)}:${1080 * Math.min(dpr, 2)}:flags=lanczos,format=yuv420p`,
		'-c:v', 'libx264', '-preset', 'slow', '-crf', '12', '-r', String(FPS), file]);
	for (let f = 0; f < n; f++) {
		const t = f / FPS - PRE;
		const ret = spec.action ? await spec.action(page, t, R, st) : undefined;
		const fr = spec.frame(t, R, ret);
		const c = fr.cam;
		await page.evaluate(([c, cur, matte]) => {
			const mt = document.getElementById('cap-matte');
			if (matte) { mt.style.display = 'block'; mt.style.width = matte + 'px'; } else { mt.style.display = 'none'; }
			document.body.style.transform = `translate(${960 - c.x * c.z}px, ${540 - c.y * c.z}px) scale(${c.z})`;
			const el = document.getElementById('cap-cursor');
			if (cur) {
				el.style.display = 'block';
				el.style.transform = `translate(${960 + (cur.x - c.x) * c.z}px, ${540 + (cur.y - c.y) * c.z}px) scale(${cur.down ? 0.9 : 1})`;
			} else { el.style.display = 'none'; }
		}, [c, fr.cursor || null, fr.matte || 0]);
		await page.clock.runFor(1000 / FPS);
		const buf = await page.screenshot({ type: 'png' });
		if (!ff.stdin.write(buf)) { await new Promise(r => ff.stdin.once('drain', r)); }
	}
	ff.stdin.end();
	await new Promise((res, rej) => ff.on('close', code => (code === 0 ? res() : rej(new Error('ffmpeg ' + code)))));
	await ctx.close();
	return { id, frames: n, errors };
}

(async () => {
	const outDir = process.argv[2];
	const dpr = parseFloat(process.argv[3] || '1');
	const ids = process.argv.slice(4).length ? process.argv.slice(4) : Object.keys(CLIPS);
	fs.mkdirSync(outDir, { recursive: true });
	const browser = await chromium.launch();
	for (const id of ids) {
		const r = await renderClip(browser, id, outDir, dpr);
		console.log(r.id, r.frames, 'frames', r.errors.length ? 'ERRORS ' + r.errors.join(' | ') : 'ok');
	}
	await browser.close();
})();
