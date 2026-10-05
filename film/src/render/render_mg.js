/* VNISES manifesto — render motion graphics (layer C) và slate tham chiếu (layer A).
 * Mỗi shot xuất thành <outDir>/<SHOT>.mp4, gồm handle PRE/POST giây hai đầu cho transition.
 * Dùng: node render_mg.js <outDir> <dpr> [shotId...]   (dpr 1 = 1080p, dpr 2 = 2160p)
 */
'use strict';
const { chromium } = require('playwright');
const { spawn } = require('child_process');
const path = require('path');
const fs = require('fs');

const FPS = 25, PRE = 0.8, POST = 0.8;
const TL = JSON.parse(fs.readFileSync(path.join(__dirname, 'timeline.json'), 'utf8'));

(async () => {
	const outDir = process.argv[2];
	const dpr = parseFloat(process.argv[3] || '1');
	const only = process.argv.slice(4);
	fs.mkdirSync(outDir, { recursive: true });
	const browser = await chromium.launch();
	const page = await browser.newPage({ viewport: { width: 1920, height: 1080 }, deviceScaleFactor: dpr });
	const errors = [];
	page.on('pageerror', e => errors.push(e.message));
	await page.goto('file://' + path.join(__dirname, 'film.html'));
	await page.evaluate(([tl, d]) => VNGT.init(tl, d), [TL, dpr]);
	const shots = TL.shots.filter(s => (only.length ? only.includes(s.id) : true) && !['B'].includes(s.layer));
	for (const s of shots) {
		const n = Math.round((s.dur + PRE + POST) * FPS);
		const file = path.join(outDir, s.id + '.mp4');
		const ff = spawn('ffmpeg', ['-y', '-loglevel', 'error', '-f', 'image2pipe', '-framerate', String(FPS), '-i', '-',
			'-vf', 'format=yuv420p', '-c:v', 'libx264', '-preset', 'slow', '-crf', dpr > 1 ? '14' : '12', '-r', String(FPS), file]);
		for (let f = 0; f < n; f++) {
			const t = f / FPS - PRE;
			await page.evaluate(([id, tt]) => VNGT.render(id, tt), [s.id, t]);
			const buf = await page.screenshot({ type: 'png' });
			if (!ff.stdin.write(buf)) { await new Promise(r => ff.stdin.once('drain', r)); }
		}
		ff.stdin.end();
		await new Promise((res, rej) => ff.on('close', c => (c === 0 ? res() : rej(new Error('ffmpeg ' + c)))));
		console.log(s.id, n, 'frames');
	}
	console.log('errors', errors.length ? errors : 'none');
	await browser.close();
})();
