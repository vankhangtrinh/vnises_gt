/* Sinh "17_Danh_sach_anh_va_nguon_tai.docx" từ dữ liệu asset layer A của film_data.py.
 * Dùng: node build_asset_docx.js <assets_A.json> <out.docx>
 */
'use strict';
const fs = require('fs');
const {
	Document, Packer, Paragraph, TextRun, Table, TableRow, TableCell, WidthType, ShadingType, BorderStyle,
	HeadingLevel, AlignmentType, ExternalHyperlink, LevelFormat, Footer, Header, PageNumber, TabStopType
} = require('docx');

const assets = JSON.parse(fs.readFileSync(process.argv[2], 'utf8'));
const OUT = process.argv[3];
const FONT = 'Arial';
const W = 9638; // A4 21 cm − lề 2 × 2 cm
const ACCENT = 'B8862E', MUTED = '5F5F5A', LINE = 'C9C8C2', HEAD_BG = 'EDEBE4', LABEL_BG = 'F6F5F1';

/* Thông tin bổ sung cho từng ảnh: từ khóa tìm và tên ngắn cho file. */
const EXTRA = {
	A01: { short: 'Bầu trời đêm trên đài quan sát ESO', kw: ['Milky Way Paranal', 'La Silla night sky', 'Very Large Telescope night'], slug: 'ESO_NightSky' },
	A02: { short: 'Bản đồ CMB của Planck', kw: ['Planck CMB', 'Planck cosmic microwave background map'], slug: 'Planck_CMB' },
	A03: { short: 'Stromatolite (vịnh Shark, Tây Úc)', kw: ['stromatolites Hamelin Pool', 'stromatolites Shark Bay'], slug: 'Stromatolites' },
	A04: { short: 'Event display va chạm tại LHC', kw: ['CMS event display', 'ATLAS event display', 'Higgs candidate event display'], slug: 'LHC_EventDisplay' },
	A05: { short: 'Hubble Ultra Deep Field (2004)', kw: ['heic0406a', 'Hubble Ultra Deep Field'], slug: 'HUDF_heic0406a' },
	A06: { short: 'Ảnh STM nguyên tử', kw: ['scanning tunneling microscope atoms', 'STM image atoms NIST'], slug: 'STM_Atoms' },
	A07: { short: 'Chromatogram giải trình tự DNA', kw: ['DNA sequencing chromatogram', 'Sanger sequencing trace'], slug: 'DNA_Chromatogram' },
	A08: { short: 'Hubble — cụm SMACS 0723 (RELICS)', kw: ['SMACS 0723', 'SMACS J0723.3-7327 RELICS'], slug: 'Hubble_SMACS0723' },
	A09: { short: 'Gương chính JWST trong phòng sạch', kw: ['Webb primary mirror cleanroom', 'James Webb mirror Goddard clean room'], slug: 'JWST_Mirror' },
	A10: { short: "Webb's First Deep Field — SMACS 0723", kw: ["Webb's First Deep Field", 'SMACS 0723 NIRCam'], slug: 'Webb_SMACS0723' },
	A11: { short: 'Earthrise — Apollo 8 (1968)', kw: ['AS08-14-2383', 'Earthrise Apollo 8'], slug: 'Earthrise_AS08-14-2383' },
	A12: { short: 'Pale Blue Dot Revisited (2020)', kw: ['PIA23645', 'Pale Blue Dot Revisited'], slug: 'PaleBlueDot_PIA23645' },
	A13: { short: 'Galileo — Sidereus Nuncius (1610)', kw: ['Sidereus Nuncius 1610', 'Galileo Moon engravings'], slug: 'Galileo_SidereusNuncius' },
	A14: { short: 'Kepler — Astronomia Nova (1609)', kw: ['Astronomia nova 1609', 'Kepler Mars orbit diagram'], slug: 'Kepler_AstronomiaNova' },
	A15: { short: 'Newton — Principia (1687)', kw: ['Philosophiae naturalis principia mathematica 1687'], slug: 'Newton_Principia' },
	A16: { short: 'Hubble — biểu đồ vận tốc–khoảng cách (1929)', kw: ['doi 10.1073/pnas.15.3.168', 'Hubble 1929 velocity distance'], slug: 'Hubble1929_PNAS' }
};

const STATUS_VI = s => {
	if (/UNKNOWN/.test(s)) { return 'UNKNOWN — chưa có nguồn rõ licence; dùng phương án thay thế nếu không tìm được'; }
	if (/TO SELECT/.test(s)) { return 'Cần chọn đúng ảnh trong bộ sưu tập, sau đó xác minh licence'; }
	return 'Đã xác định ảnh; cần xác minh URL và licence khi tải';
};
const STATUS_SHORT = s => (/UNKNOWN/.test(s) ? 'UNKNOWN' : /TO SELECT/.test(s) ? 'Chọn + xác minh' : 'Xác minh');

/* ---------- helpers ---------- */
const run = (text, o = {}) => new TextRun({ text, font: FONT, size: o.size || 20, bold: o.bold, italics: o.italics, color: o.color });
const para = (children, o = {}) => new Paragraph({ children: Array.isArray(children) ? children : [children], spacing: { after: o.after === undefined ? 80 : o.after, before: o.before || 0 }, alignment: o.align, numbering: o.numbering, keepNext: o.keepNext });
const link = (url, label) => new ExternalHyperlink({ link: url, children: [new TextRun({ text: label || url, font: FONT, size: 20, style: 'Hyperlink' })] });
const border = { style: BorderStyle.SINGLE, size: 4, color: LINE };
const borders = { top: border, bottom: border, left: border, right: border };
const cell = (children, width, o = {}) => new TableCell({
	width: { size: width, type: WidthType.DXA }, borders,
	shading: o.fill ? { type: ShadingType.CLEAR, color: 'auto', fill: o.fill } : undefined,
	margins: { top: 60, bottom: 60, left: 100, right: 100 },
	children: Array.isArray(children) ? children : [children]
});

/* Tách trường url thành các link + gợi ý tìm. */
function sourceParas(a) {
	const out = [];
	const m = a.url.match(/^(.*?)(?:\s*\(tìm:\s*(.*)\))?$/);
	const urls = (m[1] || '').split(/\s+hoặc\s+/).map(x => x.trim()).filter(x => /^https?:\/\//.test(x));
	if (!urls.length) {
		out.push(para(run('Chưa có — chọn từ kho của cơ quan khoa học/địa chất (ưu tiên USGS, Geoscience Australia) hoặc Wikimedia Commons với licence PD, CC0 hoặc CC BY.', { color: MUTED }), { after: 40 }));
	} else {
		urls.forEach((u, i) => out.push(para([run(urls.length > 1 ? (i === 0 ? 'Nguồn 1: ' : 'Nguồn 2: ') : ''), link(u)], { after: 40 })));
	}
	out.push(para([run('Kho / bộ sưu tập: ', { color: MUTED }), run(a.source)], { after: 0 }));
	return out;
}

function detailTable(a) {
	const x = EXTRA[a.id] || {};
	const L = 2500, R = W - L;
	const shotsTxt = a.used.length ? a.used.map(u => `${u.id} — vào ${u.tin}, dài ${String(u.dur).replace('.', ',')} s`) : ['—'];
	const ext = /^A1[3-6]$/.test(a.id) ? 'tif hoặc jpg' : 'tif, png hoặc jpg chất lượng cao nhất';
	const row = (label, content) => new TableRow({ children: [
		cell(para(run(label, { bold: true, size: 19 }), { after: 0 }), L, { fill: LABEL_BG }),
		cell(content, R)
	] });
	return new Table({
		width: { size: W, type: WidthType.DXA }, columnWidths: [L, R],
		rows: [
			row('Mô tả', para(run(a.desc), { after: 0 })),
			row('Dùng trong shot', shotsTxt.map(t => para(run(t), { after: 0 }))),
			row('Nguồn tải', sourceParas(a)),
			row('Từ khóa tìm', para(run((x.kw || []).join('  ·  '), { italics: true }), { after: 0 })),
			row('Tác giả / tổ chức', para(run(a.org), { after: 0 })),
			row('Giấy phép', para(run(a.license), { after: 0 })),
			row('Dòng credit bắt buộc', para(run(a.credit, { bold: true }), { after: 0 })),
			row('Trạng thái', para(run(STATUS_VI(a.status), { color: /UNKNOWN/.test(a.status) ? 'A23B2A' : undefined }), { after: 0 })),
			row('Yêu cầu file', [
				para(run('Bản gốc độ phân giải cao nhất của tổ chức công bố; không dùng bản thu nhỏ hoặc ảnh chụp màn hình.'), { after: 40 }),
				para(run('Tối thiểu 4.300 px chiều ngang cho master 4K (có push-in đến 1,12×); tối thiểu 2.150 px nếu chỉ xuất 1080p.'), { after: 40 }),
				para(run('Định dạng: ' + ext + '.'), { after: 0 })
			]),
			row('Tên file đề xuất', para(run(`VNISES_${a.id}_${x.slug || a.id}${/heic0406a|AS08|PIA23645|PNAS/.test(x.slug || '') ? '' : '_<mã ảnh của nguồn>'}.${/^A1[3-6]$/.test(a.id) ? 'tif' : 'tif / jpg'}`, { size: 19 }), { after: 0 })),
			row('Ghi chú', para(run(a.note || '—'), { after: 0 }))
		]
	});
}

/* ---------- nội dung ---------- */
const children = [];
children.push(new Paragraph({ heading: HeadingLevel.TITLE, children: [new TextRun({ text: 'Danh sách ảnh khoa học và nguồn tải', font: FONT, size: 40, bold: true })], spacing: { after: 80 } }));
children.push(para(run('VNISES — Manifesto Film · Layer A (ảnh và dữ liệu khoa học thật)', { size: 24, color: MUTED }), { after: 40 }));
children.push(para(run('Phiên bản v1.0 · 05/10/2026 · Khớp với 04_Asset_Register.csv và 11_Edit_Decision_List.csv trong gói sản xuất', { size: 18, color: MUTED }), { after: 240 }));

children.push(new Paragraph({ heading: HeadingLevel.HEADING_1, children: [run('1. Lưu ý trước khi tải', { size: 28, bold: true })], spacing: { before: 120, after: 120 } }));
[
	'Các URL trong tài liệu này CHƯA được kiểm tra trực tuyến: môi trường soạn tài liệu bị chặn truy cập tới NASA, ESA, ESO, CERN và Wikimedia. Mở từng nguồn, xác nhận ảnh còn tồn tại và đọc điều khoản trên chính trang ảnh trước khi tải.',
	'Giấy phép ghi trong bảng là chính sách chung của tổ chức. Giấy phép ghi trên trang của từng ảnh luôn được ưu tiên.',
	'Chỉ tải từ trang chính thức của tổ chức công bố hoặc thư viện lưu trữ. Không lấy ảnh từ Google Images, Pinterest, trang hình nền hay bản đăng lại không rõ nguồn.',
	'Không dùng ảnh có giấy phép phi thương mại (NC) hoặc cấm tác phẩm phái sinh (ND) khi chưa có xác nhận pháp lý.',
	'Không dùng ảnh minh họa (artist concept) hoặc ảnh do AI tạo thay cho ảnh chụp/dữ liệu thật. Không chỉnh màu làm sai dữ liệu khoa học.',
	'Mọi ảnh CC BY phải hiển thị đúng dòng credit trên màn hình (góc dưới trái, 4 giây) và trong thẻ credit cuối phim.'
].forEach(t => children.push(para(run(t), { numbering: { reference: 'bullets', level: 0 }, after: 60 })));

children.push(new Paragraph({ heading: HeadingLevel.HEADING_1, children: [run('2. Bảng tổng hợp', { size: 28, bold: true })], spacing: { before: 240, after: 120 } }));
const cw = [760, 3300, 3018, 1100, 1460];
const hdr = ['Mã', 'Ảnh', 'Tác giả / tổ chức', 'Shot', 'Trạng thái'];
const sumRows = [new TableRow({ tableHeader: true, children: hdr.map((h, i) => cell(para(run(h, { bold: true, size: 19 }), { after: 0 }), cw[i], { fill: HEAD_BG })) })];
assets.forEach(a => {
	const x = EXTRA[a.id] || {};
	sumRows.push(new TableRow({ children: [
		cell(para(run(a.id, { bold: true, size: 19 }), { after: 0 }), cw[0]),
		cell(para(run(x.short || a.desc, { size: 19 }), { after: 0 }), cw[1]),
		cell(para(run(a.org, { size: 18 }), { after: 0 }), cw[2]),
		cell(para(run(a.used.map(u => u.id).join(', ') || '—', { size: 19 }), { after: 0 }), cw[3]),
		cell(para(run(STATUS_SHORT(a.status), { size: 18, color: /UNKNOWN/.test(a.status) ? 'A23B2A' : undefined }), { after: 0 }), cw[4])
	] }));
});
children.push(new Table({ width: { size: W, type: WidthType.DXA }, columnWidths: cw, rows: sumRows }));
children.push(para(run('Tổng cộng: ' + assets.length + ' ảnh · ' + assets.filter(a => /UNKNOWN/.test(a.status)).length + ' ảnh UNKNOWN (có phương án thay thế) · ' + assets.filter(a => /TO SELECT/.test(a.status)).length + ' ảnh cần chọn đúng file trong bộ sưu tập.', { size: 18, color: MUTED }), { before: 80, after: 120 }));

children.push(new Paragraph({ heading: HeadingLevel.HEADING_1, pageBreakBefore: true, children: [run('3. Chi tiết từng ảnh', { size: 28, bold: true })], spacing: { after: 120 } }));
assets.forEach((a, i) => {
	const x = EXTRA[a.id] || {};
	children.push(new Paragraph({ heading: HeadingLevel.HEADING_2, keepNext: true, children: [new TextRun({ text: a.id + '  ', font: FONT, size: 24, bold: true, color: ACCENT }), new TextRun({ text: x.short || a.desc, font: FONT, size: 24, bold: true })], spacing: { before: i ? 280 : 80, after: 100 } }));
	children.push(detailTable(a));
});

children.push(new Paragraph({ heading: HeadingLevel.HEADING_1, pageBreakBefore: true, children: [run('4. Quy trình tải và lưu hồ sơ', { size: 28, bold: true })], spacing: { after: 120 } }));
[
	'Mở nguồn tải, tìm đúng ảnh bằng mã ảnh hoặc từ khóa trong bảng.',
	'Đối chiếu ảnh với mô tả và shot sử dụng (đúng đối tượng, đúng phiên bản; ví dụ A12 là bản xử lý lại năm 2020).',
	'Đọc điều khoản trên trang ảnh. Lưu trang đó thành PDF, đặt cùng tên file ảnh, thêm hậu tố “_license.pdf”.',
	'Tải bản gốc độ phân giải cao nhất; không chụp màn hình, không tải bản xem trước.',
	'Đổi tên theo mẫu “Tên file đề xuất”, điền mã ảnh của nguồn (ví dụ heic0406a, PIA23645).',
	'Cập nhật 04_Asset_Register.csv: URL chính xác của trang ảnh, giấy phép đã xác nhận, dòng credit đầy đủ (kể cả tên nhiếp ảnh gia), trạng thái “VERIFIED”.',
	'Cập nhật dòng credit trên màn hình và thẻ credit cuối phim (SH28) theo dòng credit đã xác nhận.',
	'Lưu toàn bộ ảnh và PDF giấy phép vào thư mục “Assets_A” cùng gói sản xuất để kiểm tra lại khi cần.'
].forEach(t => children.push(para(run(t), { numbering: { reference: 'steps', level: 0 }, after: 60 })));

children.push(new Paragraph({ heading: HeadingLevel.HEADING_1, children: [run('5. Checklist xác minh trước khi dựng', { size: 28, bold: true })], spacing: { before: 240, after: 120 } }));
[
	'Ảnh là ảnh chụp hoặc dữ liệu thật của tổ chức công bố, không phải ảnh minh họa hay ảnh AI.',
	'Giấy phép cho phép dùng trong video công khai; không có điều kiện NC/ND chưa được xử lý.',
	'Dòng credit đúng nguyên văn theo trang ảnh.',
	'Không dùng logo hoặc phù hiệu của NASA, ESA, ESO, CERN; không ngụ ý các tổ chức này bảo trợ VNISES.',
	'Độ phân giải đủ cho push-in 4K (≥ 4.300 px chiều ngang).',
	'A08 và A10 được căn chỉnh cùng trường nhìn để làm phép so sánh Hubble → Webb ở SH10.',
	'A09 có người nhận diện được: kiểm tra quyền hình ảnh cá nhân nếu dùng cho quảng bá.',
	'A16 (Hubble 1929): public domain tại Hoa Kỳ; nếu phát hành thương mại ở thị trường khác, xin ý kiến tư vấn pháp lý.'
].forEach(t => children.push(para(run('☐  ' + t), { after: 60 })));

const doc = new Document({
	creator: 'VNISES',
	title: 'VNISES Manifesto Film — Danh sách ảnh khoa học và nguồn tải',
	styles: {
		default: { document: { run: { font: FONT, size: 20 } } },
		paragraphStyles: [
			{ id: 'Heading1', name: 'Heading 1', basedOn: 'Normal', next: 'Normal', quickFormat: true, run: { font: FONT, size: 28, bold: true }, paragraph: { spacing: { before: 240, after: 120 }, outlineLevel: 0 } },
			{ id: 'Heading2', name: 'Heading 2', basedOn: 'Normal', next: 'Normal', quickFormat: true, run: { font: FONT, size: 24, bold: true }, paragraph: { spacing: { before: 200, after: 100 }, outlineLevel: 1 } }
		]
	},
	numbering: { config: [
		{ reference: 'bullets', levels: [{ level: 0, format: LevelFormat.BULLET, text: '•', alignment: AlignmentType.LEFT, style: { paragraph: { indent: { left: 540, hanging: 300 } } } }] },
		{ reference: 'steps', levels: [{ level: 0, format: LevelFormat.DECIMAL, text: '%1.', alignment: AlignmentType.LEFT, style: { paragraph: { indent: { left: 540, hanging: 360 } } } }] }
	] },
	sections: [{
		properties: { page: { size: { width: 11906, height: 16838 }, margin: { top: 1134, bottom: 1134, left: 1134, right: 1134 } } },
		headers: { default: new Header({ children: [new Paragraph({ alignment: AlignmentType.RIGHT, children: [new TextRun({ text: 'VNISES · Manifesto Film · Layer A', font: FONT, size: 16, color: MUTED })] })] }) },
		footers: { default: new Footer({ children: [new Paragraph({ alignment: AlignmentType.CENTER, children: [new TextRun({ children: ['Trang ', PageNumber.CURRENT, ' / ', PageNumber.TOTAL_PAGES], font: FONT, size: 16, color: MUTED })] })] }) },
		children
	}]
});

Packer.toBuffer(doc).then(b => { fs.writeFileSync(OUT, b); console.log('wrote', OUT, b.length, 'bytes'); });
