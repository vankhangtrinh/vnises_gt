<?php
/**
 * Plugin Name:       VNISES — Giới thiệu
 * Description:       Phần giới thiệu chính thức của VNISES dạng narrative scrolling (9 scene). Shortcode: [vnises_gioithieu]
 * Version:           1.0.0
 * Requires at least: 5.2
 * Requires PHP:      7.0
 * Author:            VNISES
 * License:           GPL-2.0-or-later
 * Text Domain:       vnises-gioithieu
 *
 * vnises-gioithieu.php — một file duy nhất: PHP + HTML + CSS + JS.
 *
 * Cách dùng: đặt file vào wp-content/plugins/ (rồi kích hoạt) hoặc nạp như một snippet PHP,
 * sau đó chèn shortcode [vnises_gioithieu] vào trang. Tùy chọn: [vnises_gioithieu heading="1"].
 *
 * Kiến trúc nội bộ (có thể tách thành file riêng về sau):
 *   1. SECURITY / CONFIGURATION   — vnises_gt_config()
 *   2. CONTENT / DATA              — vnises_gt_content()
 *   3. SHORTCODE REGISTRATION      — vnises_gt_register_shortcode(), vnises_gt_shortcode()
 *   4. HTML RENDERER               — vnises_gt_render_*() + vnises_gt_svg_*()
 *   5. SCOPED CSS                  — vnises_gt_css()
 *   6. JAVASCRIPT                  — vnises_gt_js()
 *   7. ACCESSIBILITY SUPPORT       — helper heading/ARIA + quy ước trong CSS/JS
 *   8. INITIALIZATION / FALLBACK   — in assets một lần, khởi tạo, fallback khi không có JS
 *
 * Nguyên tắc nội dung: mọi hình trong module là mô hình minh họa (SIMULATION) hoặc sơ đồ (SƠ ĐỒ).
 * Module không gọi API, không có dữ liệu trực tiếp, không có timestamp hay nguồn giả.
 */

/* =============================================================================
 * 1. SECURITY / CONFIGURATION
 * ========================================================================== */

defined( 'ABSPATH' ) || exit;

// File có thể vô tình được nạp hai lần (plugin + snippet): dừng lần nạp thứ hai để tránh trùng hàm.
if ( defined( 'VNISES_GT_VERSION' ) ) {
	return;
}
define( 'VNISES_GT_VERSION', '1.0.0' );

/**
 * Cấu hình — chỉ cần sửa tại đây.
 *
 * @return array
 */
function vnises_gt_config() {
	$config = array(
		// URL của nút "Bắt đầu khám phá". Để trống '' = trang chủ website (home_url).
		// Ví dụ: 'cta_url' => 'https://vnises.vn/kham-pha/',
		'cta_url'       => '',
		'cta_label'     => 'Bắt đầu khám phá',
		// Cấp heading cao nhất của module (1–3). Mặc định 2 vì trang WordPress thường đã có H1 (tiêu đề trang).
		'heading_level' => 2,
		// true: module tràn hết chiều ngang cửa sổ khi cột nội dung của theme được căn giữa.
		// Nếu cột nội dung lệch (ví dụ có sidebar), module tự giữ nguyên trong cột để không đè lên sidebar.
		'full_bleed'    => true,
		// Khoảng bù (px) khi nhảy tới một phần qua mục lục — tăng nếu theme có header cố định.
		'scroll_offset' => 24,
	);

	/** Cho phép ghi đè cấu hình bằng filter 'vnises_gt_config' mà không sửa file. */
	$config = apply_filters( 'vnises_gt_config', $config );

	return is_array( $config ) ? $config : array();
}

/**
 * Chuẩn hóa cấp heading về khoảng 1–3 (cấp con sâu nhất là +2, tức tối đa H5).
 *
 * @param mixed $value Giá trị thô.
 * @return int
 */
function vnises_gt_sanitize_level( $value ) {
	$level = absint( $value );
	if ( $level < 1 || $level > 3 ) {
		$level = 2;
	}
	return $level;
}

/**
 * URL của CTA (đã escape ở bước render).
 *
 * @param array $config Cấu hình.
 * @return string
 */
function vnises_gt_cta_url( $config ) {
	$url = isset( $config['cta_url'] ) ? trim( (string) $config['cta_url'] ) : '';
	if ( '' === $url ) {
		$url = home_url( '/' );
	}
	return $url;
}

/* =============================================================================
 * 2. CONTENT / DATA
 * Toàn bộ văn bản của module. Chuỗi có thể chứa inline markup tối thiểu
 * (em, i, sup, sub, span[class|lang]) — được lọc bằng wp_kses khi render.
 * ========================================================================== */

/**
 * @return array
 */
function vnises_gt_content() {
	return array(

		'module'      => array(
			'title'     => 'Giới thiệu VNISES',
			'expansion' => 'Vietnam Nexus for Interactive Space Exploration and Science',
			'nav_label' => 'Mục lục phần giới thiệu VNISES',
			'nav_title' => 'Chín phần của phần giới thiệu',
		),

		/* ---------------------------------------------------------------- 01 */
		'opening'     => array(
			'nav'      => 'Khám phá',
			'eyebrow'  => 'Khoa học có thể khám phá',
			'title'    => 'Khoa học trở nên gần gũi hơn khi những điều tưởng như trừu tượng có thể được <em class="vngt-em">nhìn thấy, thao tác và kiểm chứng</em>.',
			'examples' => array(
				array(
					'key'     => 'orbit',
					'label'   => 'Quỹ đạo',
					'claim'   => 'không chỉ là một đường cong trong sách.',
					'type'    => 'SIMULATION',
					'note'    => 'mô hình minh họa',
					'aria'    => 'Mô hình minh họa: quỹ đạo elip với vật trung tâm tại tiêu điểm F và các vùng diện tích quét được trong cùng khoảng thời gian Δt.',
					'caption' => 'Một vật chuyển động trên quỹ đạo elip quanh vật trung tâm đặt tại tiêu điểm F. Vùng tô sáng là diện tích mà đoạn thẳng nối hai vật quét được trong cùng một khoảng thời gian Δt: hẹp và dài ở viễn điểm, rộng và ngắn ở cận điểm, nhưng có diện tích bằng nhau — định luật thứ hai của Kepler.',
				),
				array(
					'key'      => 'oscillation',
					'label'    => 'Hiện tượng vật lý',
					'claim'    => 'không chỉ là một phương trình.',
					'type'     => 'SIMULATION',
					'note'     => 'đồ thị tính từ phương trình',
					'aria'     => 'Đồ thị dao động tắt dần: đường dao động nằm giữa hai đường bao giảm dần theo hàm mũ.',
					'equation' => '<i>x</i>(<i>t</i>) = <i>A</i>·e<sup>−<i>γt</i></sup>·cos(<i>ωt</i>)',
					'caption'  => 'Dao động tắt dần: khi phương trình được vẽ thành đồ thị, có thể thấy biên độ giảm dần theo thời gian trong khi chu kỳ dao động không đổi. Đường nét đứt là đường bao ±<i>A</i>·e<sup>−<i>γt</i></sup>. Đồ thị định tính, không gắn đơn vị.',
				),
				array(
					'key'     => 'sun',
					'label'   => 'Mặt Trời',
					'claim'   => 'không chỉ là một đĩa sáng trên bầu trời.',
					'type'    => 'SƠ ĐỒ',
					'note'    => 'tỉ lệ gần đúng',
					'aria'    => 'Sơ đồ Mặt Trời: đĩa sáng tối dần về phía rìa và mặt cắt một phần tư cho thấy lõi, vùng bức xạ và vùng đối lưu.',
					'caption' => 'Đĩa Mặt Trời tối dần từ tâm ra rìa (hiệu ứng tối rìa), vì ở gần rìa ta nhìn thấy các lớp khí nông hơn và nguội hơn. Mặt cắt cho thấy cấu trúc bên trong — lõi, vùng bức xạ, vùng đối lưu — với bán kính tương đối gần đúng.',
				),
			),
			'closing'  => 'Khi được quan sát bằng dữ liệu, mô hình và tương tác, những khái niệm đó trở thành những thế giới có thể khám phá.',
			'triad'    => array( 'Dữ liệu', 'Mô hình', 'Tương tác' ),
		),

		/* ---------------------------------------------------------------- 02 */
		'identity'    => array(
			'nav'       => 'VNISES',
			'eyebrow'   => 'Danh tính',
			'name'      => 'VNISES',
			// Mỗi phần tử: array( chữ cái đầu được nhấn, phần còn lại ). Chữ cái đầu rỗng = từ nối.
			'expansion' => array(
				array( 'V', 'ietnam' ),
				array( 'N', 'exus' ),
				array( '', 'for' ),
				array( 'I', 'nteractive' ),
				array( 'S', 'pace' ),
				array( 'E', 'xploration' ),
				array( '', 'and' ),
				array( 'S', 'cience' ),
			),
			'lead'      => 'Được xây dựng từ cách tiếp cận đó, VNISES là một nền tảng trực tuyến về khám phá khoa học và công nghệ vũ trụ, nơi nội dung khoa học được kết nối với trực quan hóa, mô phỏng, tương tác và dữ liệu thực hoặc gần thời gian thực.',
			'pillars'   => array(
				array( 'key' => 'viz', 'name' => 'Trực quan hóa', 'text' => 'Những khái niệm khó hình dung được chuyển thành hình ảnh, đồ thị và mô hình.' ),
				array( 'key' => 'sim', 'name' => 'Mô phỏng', 'text' => 'Mô hình có thể thao tác để theo dõi cơ chế đứng phía sau một hiện tượng.' ),
				array( 'key' => 'int', 'name' => 'Tương tác', 'text' => 'Người dùng thay đổi tham số, so sánh kết quả và kiểm tra trực giác của mình.' ),
				array( 'key' => 'dat', 'name' => 'Dữ liệu', 'text' => 'Khi phù hợp, dữ liệu quan sát thực hoặc gần thời gian thực được đưa vào trải nghiệm.' ),
			),
		),

		/* ---------------------------------------------------------------- 03 */
		'nexus'       => array(
			'nav'       => 'Nexus',
			'eyebrow'   => 'Nexus',
			'title'     => '<span lang="en">Nexus</span> — một điểm hội tụ',
			'body'      => array(
				'“Nexus” thể hiện vai trò của VNISES như một điểm hội tụ: giữa các lĩnh vực khoa học, giữa lý thuyết và quan sát, giữa mô hình và thực tế, giữa kiến thức và trải nghiệm.',
				'Thay vì tổ chức khoa học thành những mảnh thông tin rời rạc, VNISES hướng tới xây dựng một không gian trong đó mỗi câu hỏi có thể dẫn sang một hiện tượng, một mô hình, một thử nghiệm hoặc một hành trình khám phá sâu hơn.',
			),
			'diagram_label' => 'Sơ đồ Nexus: bốn cặp khái niệm, mỗi quan hệ đều đi qua cùng một điểm hội tụ',
			'hint'      => 'Chọn một khái niệm trong sơ đồ để xem quan hệ của nó.',
			'core'      => 'NEXUS',
			'relations' => array(
				array( 'a' => 'Lý thuyết', 'b' => 'Quan sát', 'text' => 'Lý thuyết đưa ra dự đoán; quan sát kiểm tra dự đoán đó — và thường đặt ra câu hỏi mới.' ),
				array( 'a' => 'Mô hình', 'b' => 'Thực tế', 'text' => 'Mô hình là một sự đơn giản hóa có chủ đích; đối chiếu với thực tế cho biết mô hình đúng đến đâu.' ),
				array( 'a' => 'Kiến thức', 'b' => 'Trải nghiệm', 'text' => 'Kiến thức trở nên vững hơn khi người học tự tay thao tác, thử và kiểm tra.' ),
				array( 'a' => 'Khoa học', 'b' => 'Công nghệ', 'text' => 'Hiểu biết khoa học tạo nền cho công nghệ; công nghệ mở ra những quan sát mới.' ),
			),
			'ring_note' => 'Vòng ngoài tượng trưng cho các lĩnh vực khoa học (xem phần 05). Mọi quan hệ trong sơ đồ đều đi qua cùng một điểm hội tụ.',
			'question'  => array(
				'label'    => 'Ví dụ: một câu hỏi, nhiều hướng đi',
				'text'     => '“Vì sao vệ tinh không rơi xuống Trái Đất?”',
				'branches' => array(
					array( 'Hiện tượng', 'Vệ tinh thực ra luôn rơi về phía Trái Đất, nhưng đồng thời chuyển động ngang đủ nhanh để liên tục “trượt qua” bề mặt.' ),
					array( 'Mô hình', 'Chuyển động trên quỹ đạo dưới tác dụng của lực hấp dẫn.' ),
					array( 'Thử nghiệm', 'Thay đổi vận tốc ban đầu và quan sát quỹ đạo thay đổi theo.' ),
					array( 'Đi sâu hơn', 'Quỹ đạo elip và các định luật Kepler — như mô hình ở phần 04.' ),
				),
			),
		),

		/* ---------------------------------------------------------------- 04 */
		'method'      => array(
			'nav'     => 'Phương pháp',
			'eyebrow' => 'Phương pháp',
			'title'   => 'Không chỉ đọc về khoa học',
			'body'    => array(
				'Người dùng không chỉ đọc về khoa học. Họ có thể quan sát, tương tác, thay đổi tham số, theo dõi kết quả và từng bước hiểu cơ chế đứng phía sau một hiện tượng.',
				'Những khái niệm khó hình dung được ưu tiên chuyển thành hình ảnh, đồ thị, mô hình và mô phỏng có thể thao tác. Khi phù hợp, dữ liệu quan sát thực được đưa trực tiếp vào trải nghiệm để kết nối kiến thức với thế giới đang thực sự vận động ngoài màn hình.',
			),
			'steps'   => array(
				array( 'Quan sát', 'Nhìn hình dạng quỹ đạo và vị trí của vật trung tâm.' ),
				array( 'Thay đổi', 'Kéo thanh trượt để thay đổi độ lệch tâm e.' ),
				array( 'So sánh', 'Đối chiếu với quỹ đạo tham chiếu (nét đứt).' ),
				array( 'Kiểm chứng', 'Đọc các tỉ số và đối chiếu với công thức.' ),
				array( 'Hiểu', 'Giải thích vì sao vật đi nhanh ở cận điểm.' ),
			),
			'lab'     => array(
				'title'      => 'Độ lệch tâm của một quỹ đạo',
				'type'       => 'SIMULATION',
				'note'       => 'mô hình minh họa — không phải dữ liệu thực',
				'aria'       => 'Mô hình minh họa quỹ đạo Kepler: quỹ đạo hiện tại, quỹ đạo tham chiếu nét đứt, vật trung tâm tại tiêu điểm F và mười hai vị trí cách đều nhau theo thời gian.',
				'slider'     => 'Độ lệch tâm <i>e</i>',
				'help'       => '<i>e</i> là đại lượng không thứ nguyên: <i>e</i> = 0 là đường tròn; <i>e</i> càng gần 1, elip càng dẹt. Khoảng trong mô hình: 0 – 0,85.',
				'min_label'  => '0 · tròn',
				'max_label'  => '0,85 · dẹt',
				'pin'        => 'Ghim làm quỹ đạo tham chiếu',
				'reset'      => 'Đặt lại',
				'nojs'       => 'Hình đang hiển thị quỹ đạo e = 0,50 so với quỹ đạo tròn tham chiếu e = 0. Bật JavaScript để tự thay đổi e.',
				'rows'       => array(
					'cur'   => 'Quỹ đạo hiện tại',
					'ref'   => 'Quỹ đạo tham chiếu',
					'ba'    => 'Tỉ số bán trục <i>b</i>/<i>a</i> = √(1 − <i>e</i>²)',
					'rr'    => 'Khoảng cách <i>r</i><sub>cận</sub>/<i>r</i><sub>viễn</sub> = (1 − <i>e</i>)/(1 + <i>e</i>)',
					'vv'    => 'Tốc độ <i>v</i><sub>cận</sub>/<i>v</i><sub>viễn</sub> = (1 + <i>e</i>)/(1 − <i>e</i>)',
				),
				'legend'     => array(
					'cur'  => 'Quỹ đạo hiện tại',
					'ref'  => 'Quỹ đạo tham chiếu',
					'tick' => '12 vị trí cách đều nhau theo thời gian',
					'body' => 'Vật trung tâm tại tiêu điểm F',
					'vel'  => 'Vectơ vận tốc (độ dài tỉ lệ với tốc độ)',
				),
				'verify'     => '<strong>Thử kiểm chứng.</strong> Khi <i>e</i> tăng, các vị trí cách đều theo thời gian dồn về phía viễn điểm: vật đi chậm khi ở xa và nhanh khi ở gần vật trung tâm. Vì bán trục lớn <i>a</i> được giữ cố định, chu kỳ quỹ đạo không đổi khi thay đổi <i>e</i> (định luật thứ ba của Kepler).',
				'model'      => 'Mô hình: bài toán hai vật lý tưởng theo các định luật Kepler; vật trung tâm và bán trục lớn <i>a</i> giữ cố định; thời gian được chuẩn hóa nên tốc độ trên màn hình không theo tỉ lệ thực. Mô hình không biểu diễn một vật thể cụ thể nào.',
				'labels'     => array( 'peri' => 'cận điểm', 'apo' => 'viễn điểm' ),
			),
		),

		/* ---------------------------------------------------------------- 05 */
		'landscape'   => array(
			'nav'      => 'Lĩnh vực',
			'eyebrow'  => 'Phạm vi khoa học',
			'title'    => 'Từ Trái Đất và bầu trời đến lịch sử tiến hóa của vũ trụ',
			'body'     => array(
				'Phạm vi của VNISES trải từ Trái Đất và bầu trời, các hiện tượng của thế giới tự nhiên, công nghệ không gian và thế giới lượng tử đến không gian – thời gian, du hành vũ trụ, thiên hà và lịch sử tiến hóa của vũ trụ.',
				'Các lĩnh vực này được kết nối trong một hệ thống khám phá nhiều tầng: đủ trực quan để người mới có thể bắt đầu, nhưng đủ chiều sâu để người dùng tiếp tục đi tới mô hình, cơ chế vật lý và ứng dụng.',
			),
			'map_note' => 'Bản đồ không có thứ bậc: không lĩnh vực nào là gốc hay nhánh của lĩnh vực khác. Vị trí chỉ mang tính bố cục; các đường nối thể hiện một số liên kết khoa học tiêu biểu.',
			'domains_label' => 'Tám lĩnh vực',
			'domains'  => array(
				'earth'     => array( 'name' => 'Trái Đất & Bầu trời', 'text' => 'Hành tinh của chúng ta, khí quyển và bầu trời quan sát được từ mặt đất.', 'x' => 175, 'y' => 150, 'lp' => 'top' ),
				'nature'    => array( 'name' => 'Thế giới tự nhiên', 'text' => 'Các hiện tượng quen thuộc: ánh sáng, âm thanh, nhiệt, chuyển động, vật chất.', 'x' => 135, 'y' => 385, 'lp' => 'bottom' ),
				'spacetech' => array( 'name' => 'Công nghệ không gian', 'text' => 'Vệ tinh, tên lửa, trạm vũ trụ và các hệ thống hoạt động ngoài khí quyển.', 'x' => 390, 'y' => 255, 'lp' => 'top' ),
				'quantum'   => array( 'name' => 'Thế giới lượng tử', 'text' => 'Nguyên tử, photon và các quy luật ở thang vi mô.', 'x' => 400, 'y' => 470, 'lp' => 'bottom' ),
				'spacetime' => array( 'name' => 'Không gian & Thời gian', 'text' => 'Thuyết tương đối, hấp dẫn và cấu trúc của không – thời gian.', 'x' => 610, 'y' => 105, 'lp' => 'top' ),
				'travel'    => array( 'name' => 'Du hành vũ trụ', 'text' => 'Quỹ đạo, quỹ đạo chuyển tiếp và hành trình tới các thiên thể khác.', 'x' => 635, 'y' => 330, 'lp' => 'bottom' ),
				'galaxies'  => array( 'name' => 'Thiên hà', 'text' => 'Sao, tinh vân, thiên hà và cấu trúc quy mô lớn.', 'x' => 845, 'y' => 215, 'lp' => 'top' ),
				'cosmos'    => array( 'name' => 'Vũ trụ', 'text' => 'Nguồn gốc và lịch sử tiến hóa của vũ trụ.', 'x' => 860, 'y' => 440, 'lp' => 'bottom' ),
			),
			// Liên kết nền (không hướng, cùng trọng số — tránh gợi ý thứ bậc).
			'edges'    => array(
				array( 'earth', 'nature' ), array( 'earth', 'spacetech' ), array( 'earth', 'spacetime' ),
				array( 'nature', 'quantum' ), array( 'nature', 'spacetech' ), array( 'spacetech', 'quantum' ),
				array( 'spacetech', 'travel' ), array( 'spacetime', 'travel' ), array( 'spacetime', 'galaxies' ),
				array( 'spacetime', 'cosmos' ), array( 'quantum', 'cosmos' ), array( 'galaxies', 'cosmos' ),
				array( 'quantum', 'galaxies' ),
			),
			'threads_label' => 'Mạch liên kết xuyên lĩnh vực',
			'threads_hint'  => 'Chọn một mạch để làm nổi đường đi của nó qua các lĩnh vực.',
			'threads'  => array(
				array(
					'name'  => 'Ánh sáng',
					'chain' => array( 'nature', 'quantum', 'galaxies', 'cosmos' ),
					'text'  => 'Ánh sáng Mặt Trời tách thành dải màu trong cầu vồng; các vạch phổ hẹp được giải thích bằng photon và các mức năng lượng của nguyên tử; nhờ các vạch phổ đó, ta biết thành phần và chuyển động của sao và thiên hà; bức xạ nền vi sóng vũ trụ mang thông tin về vũ trụ sơ khai.',
				),
				array(
					'name'  => 'Hấp dẫn',
					'chain' => array( 'spacetime', 'travel', 'galaxies' ),
					'text'  => 'Trong thuyết tương đối rộng, hấp dẫn được mô tả bằng độ cong của không – thời gian. Cũng chính hấp dẫn quyết định quỹ đạo của vệ tinh, hành trình của tàu vũ trụ và chuyển động của sao trong thiên hà.',
				),
				array(
					'name'  => 'Vật liệu',
					'chain' => array( 'nature', 'spacetech', 'travel' ),
					'text'  => 'Tính chất của vật chất trong tự nhiên — dẫn nhiệt, chịu nhiệt, chịu bức xạ — quyết định việc chọn vật liệu cho vệ tinh và cho lớp chắn nhiệt bảo vệ tàu vũ trụ khi trở lại khí quyển.',
				),
			),
			'layers_label' => 'Hệ thống khám phá nhiều tầng',
			'layers'   => array(
				array( 'Trực quan', 'điểm bắt đầu cho người mới' ),
				array( 'Mô hình', 'đơn giản hóa có kiểm soát' ),
				array( 'Cơ chế vật lý', 'vì sao hiện tượng xảy ra' ),
				array( 'Ứng dụng', 'kiến thức đi vào công nghệ' ),
			),
		),

		/* ---------------------------------------------------------------- 06 */
		'interaction' => array(
			'nav'      => 'Tương tác',
			'eyebrow'  => 'Tương tác',
			'title'    => 'Tương tác không phải là hiệu ứng trình diễn. <span class="vngt-title__second">Nó là một phần của phương pháp khám phá.</span>',
			'body'     => 'Tính tương tác là một phần cốt lõi của VNISES, không chỉ là hiệu ứng trình diễn. Người dùng có thể bắt đầu từ một quan sát, đặt câu hỏi, thay đổi điều kiện, so sánh kết quả và kiểm tra trực giác của mình.',
			'loop_label' => 'Vòng khám phá',
			'loop'     => array( 'Quan sát', 'Đặt câu hỏi', 'Thay đổi điều kiện', 'So sánh', 'Kiểm tra trực giác' ),
			'loop_note' => 'Một câu trả lời thường mở ra một quan sát mới — và vòng khám phá tiếp tục.',
			'station'  => array(
				'title'      => 'Trạm Vũ trụ',
				'body'       => 'Những trải nghiệm như mô phỏng khoa học hay Trạm Vũ trụ — nơi kết hợp hình ảnh trực tiếp từ ISS, chuyển động quỹ đạo vệ tinh và quan sát Mặt Trời — được xây dựng theo cùng một nguyên tắc: biến kiến thức thành thứ có thể trực tiếp khám phá.',
				'notice'     => 'Phần này giới thiệu cấu trúc của trải nghiệm Trạm Vũ trụ. Module giới thiệu không kết nối tới nguồn dữ liệu nào: các hình dưới đây là sơ đồ và mô hình minh họa, không phải hình ảnh hay dữ liệu trực tiếp.',
				'notice_tag' => 'Xem trước',
				'components' => array(
					array(
						'key'     => 'iss',
						'micro'   => 'ISS',
						'title'   => 'Hình ảnh ISS',
						'text'    => 'Hình ảnh trực tiếp từ Trạm Vũ trụ Quốc tế khi nguồn phát khả dụng: Trái Đất nhìn từ quỹ đạo thấp.',
						'type'    => 'SƠ ĐỒ',
						'note'    => 'tỉ lệ gần đúng',
						'aria'    => 'Sơ đồ mặt cắt: bề mặt Trái Đất, lớp khí quyển mỏng và quỹ đạo của ISS, vẽ theo tỉ lệ gần đúng với bán kính Trái Đất.',
						'caption' => 'Mặt cắt: bề mặt Trái Đất, khí quyển (lấy mốc quy ước khoảng 100 km) và quỹ đạo ISS (khoảng 400 km), vẽ theo tỉ lệ gần đúng với bán kính Trái Đất (khoảng 6.371 km).',
					),
					array(
						'key'     => 'track',
						'micro'   => 'Quỹ đạo vệ tinh',
						'title'   => 'Theo dõi quỹ đạo',
						'text'    => 'Theo dõi chuyển động quỹ đạo của vệ tinh và vết quỹ đạo của chúng trên bề mặt Trái Đất.',
						'type'    => 'SIMULATION',
						'note'    => 'mô hình minh họa',
						'aria'    => 'Mô hình minh họa: vết quỹ đạo trên mặt đất của một quỹ đạo tròn nghiêng 51,6 độ qua hai vòng, trên lưới kinh độ – vĩ độ.',
						'caption' => 'Vết trên mặt đất của một quỹ đạo tròn nghiêng 51,6° so với xích đạo — bằng độ nghiêng quỹ đạo của ISS — qua hai vòng. Vì Trái Đất tự quay, vòng sau lệch về phía tây. Nét đứt: vĩ độ ±51,6°.',
					),
					array(
						'key'     => 'sunobs',
						'micro'   => 'Mặt Trời',
						'title'   => 'Quan sát Mặt Trời',
						'text'    => 'Quan sát Mặt Trời qua hình ảnh và dữ liệu từ các đài quan sát.',
						'type'    => 'SƠ ĐỒ',
						'note'    => 'giản lược',
						'aria'    => 'Sơ đồ: đĩa Mặt Trời với lưới vĩ độ – kinh độ và trục quay.',
						'caption' => 'Đĩa Mặt Trời với lưới vĩ độ – kinh độ trên Mặt Trời: khung tham chiếu để định vị và theo dõi các cấu trúc bề mặt, như vết đen, khi Mặt Trời tự quay.',
					),
				),
			),
		),

		/* ---------------------------------------------------------------- 07 */
		'integrity'   => array(
			'nav'      => 'Kiểm chứng',
			'eyebrow'  => 'Tính kiểm chứng',
			'title'    => 'Khoa học phải có thể kiểm chứng.',
			'body'     => 'Độ chính xác và khả năng kiểm chứng là nền tảng của toàn bộ hệ thống. VNISES ưu tiên các nguồn khoa học đáng tin cậy, dữ liệu gốc và các tổ chức có thẩm quyền; đồng thời phân biệt rõ giữa quan sát, mô phỏng, giả định và suy luận.',
			'taxo_label' => 'Phân biệt loại thông tin',
			'taxo'     => array(
				array( 'key' => 'obs', 'en' => 'OBSERVATION', 'vi' => 'Quan sát', 'text' => 'Quan sát hoặc dữ liệu đo trực tiếp bằng thiết bị.' ),
				array( 'key' => 'dat', 'en' => 'DATA', 'vi' => 'Dữ liệu', 'text' => 'Dữ liệu lấy từ một nguồn xác định; có thể đã qua hiệu chỉnh, xử lý hoặc tổng hợp.' ),
				array( 'key' => 'sim', 'en' => 'SIMULATION', 'vi' => 'Mô phỏng', 'text' => 'Kết quả do mô hình hoặc mô phỏng tạo ra; phụ thuộc vào phương trình, tham số và giả định.' ),
				array( 'key' => 'asm', 'en' => 'ASSUMPTION', 'vi' => 'Giả định', 'text' => 'Điều kiện mà mô hình chấp nhận để có thể tính toán; xác định phạm vi mà kết quả còn có ý nghĩa.' ),
				array( 'key' => 'inf', 'en' => 'INFERENCE', 'vi' => 'Suy luận', 'text' => 'Kết luận rút ra từ dữ liệu, mô hình hoặc bằng chứng; độ tin cậy phụ thuộc vào chất lượng của các cơ sở đó.' ),
			),
			'nuance'   => 'Trong thực tế, ranh giới giữa các loại này không phải lúc nào cũng tuyệt đối: nhiều dữ liệu quan sát đã được hiệu chỉnh bằng mô hình, và nhiều đại lượng “đo được” thực chất là kết quả suy luận. Nhãn không thay thế cho mô tả — nó giúp người đọc biết cần đặt câu hỏi nào.',
			'chart'    => array(
				'type'    => 'SƠ ĐỒ',
				'note'    => 'minh họa khái niệm, không phải dữ liệu',
				'aria'    => 'Sơ đồ khái niệm: các điểm quan sát và dữ liệu có thanh sai số, một đường mô hình, một ranh giới giả định và một vùng suy luận ngoại suy với độ bất định tăng dần.',
				'caption' => 'Cùng một hình có thể chứa nhiều loại thông tin, và mỗi loại cần được nhận diện riêng. Các điểm và đường trong sơ đồ không biểu diễn số liệu thực.',
				'x'       => 'tham số',
				'y'       => 'đại lượng',
			),
			'prov'     => array(
				'title' => 'Ví dụ cấu trúc provenance',
				'intro' => 'Nội dung phụ thuộc vào nguồn, thời điểm hoặc điều kiện mô hình cần đi kèm thông tin nguồn gốc. Ví dụ dưới đây áp dụng cấu trúc đó cho chính mô hình quỹ đạo minh họa ở phần 04 — không phải cho một nguồn dữ liệu bên ngoài.',
				'head'  => array( 'Trường', 'Cần nêu', 'Áp dụng cho mô hình ở phần 04' ),
				'rows'  => array(
					array( 'SOURCE', 'Nguồn', 'Tổ chức, thiết bị hoặc bộ dữ liệu gốc.', 'Không có nguồn dữ liệu bên ngoài. Hình được tính trực tiếp từ các định luật Kepler cho bài toán hai vật.' ),
					array( 'TIMESTAMP / UPDATE CONTEXT', 'Thời điểm', 'Thời điểm thu nhận hoặc cập nhật, kèm múi giờ; với dữ liệu gần thời gian thực là cả độ trễ.', 'Không phụ thuộc thời điểm: kết quả chỉ phụ thuộc vào tham số e.' ),
					array( 'DATA TYPE', 'Loại thông tin', 'Quan sát, dữ liệu, mô phỏng, giả định hay suy luận.', 'SIMULATION — mô hình minh họa.' ),
					array( 'MODEL CONDITIONS', 'Điều kiện mô hình', 'Tham số, giả định và phiên bản mô hình.', 'Hai vật lý tưởng; bán trục lớn a cố định; bỏ qua nhiễu loạn; thời gian chuẩn hóa; 0 ≤ e ≤ 0,85.' ),
					array( 'LIMITATIONS', 'Giới hạn', 'Phạm vi áp dụng, độ bất định và những gì nội dung không thể hiện.', 'Không biểu diễn vật thể thực nào; không có đơn vị vật lý; tốc độ trên màn hình không theo tỉ lệ thực.' ),
				),
			),
			'statement' => 'Khi thông tin phụ thuộc vào thời điểm, nguồn dữ liệu hoặc điều kiện mô hình, những giới hạn đó phải được thể hiện một cách minh bạch.',
			'figures_note' => 'Trong phần giới thiệu này, mỗi hình đều được gắn nhãn: SIMULATION cho mô hình minh họa, SƠ ĐỒ cho hình minh họa khái niệm. Không hình nào hiển thị dữ liệu quan sát trực tiếp.',
		),

		/* ---------------------------------------------------------------- 08 */
		'standards'   => array(
			'nav'      => 'Tiêu chuẩn',
			'eyebrow'  => 'Định hướng chất lượng',
			'title'    => 'Hướng tới tiêu chuẩn quốc tế',
			'status'   => 'Định hướng phát triển',
			'status_note' => 'Nội dung dưới đây mô tả mục tiêu mà VNISES theo đuổi — không phải thành tích đã đạt được hay đã được chứng nhận.',
			'body'     => 'VNISES được phát triển với mục tiêu đạt tiêu chuẩn quốc tế về chất lượng khoa học, trực quan hóa, trải nghiệm tương tác, công nghệ và khả năng mở rộng; hướng tới trở thành một trong những nền tảng trực tuyến hàng đầu về khám phá khoa học và công nghệ vũ trụ.',
			'col_head' => array( 'Trụ cột', 'Định hướng' ),
			'pillars'  => array(
				array( 'Chất lượng khoa học', 'Nguồn đáng tin cậy, loại thông tin được phân biệt rõ, giới hạn được nêu minh bạch.' ),
				array( 'Trực quan hóa', 'Hình ảnh phục vụ sự hiểu; đúng về hình học và tỉ lệ khi có thể, ghi rõ khi chỉ là sơ đồ.' ),
				array( 'Tương tác', 'Mỗi tương tác giúp kiểm tra hoặc khám phá một điều cụ thể.' ),
				array( 'Khả năng tiếp cận', 'Dùng được bằng bàn phím và trình đọc màn hình, đủ độ tương phản, tôn trọng thiết lập giảm chuyển động.' ),
				array( 'Hiệu năng', 'Tải nhanh, chỉ dùng tài nguyên khi cần, hoạt động tốt trên thiết bị di động.' ),
				array( 'Công nghệ', 'Tiêu chuẩn web mở, mã nguồn có cấu trúc, có thể bảo trì và kiểm thử.' ),
				array( 'Khả năng mở rộng', 'Bổ sung lĩnh vực, mô hình và nguồn dữ liệu mà không phá vỡ cấu trúc chung.' ),
			),
		),

		/* ---------------------------------------------------------------- 09 */
		'manifesto'   => array(
			'nav'     => 'Tuyên ngôn',
			'eyebrow' => 'Tuyên ngôn',
			'first'   => array( 'VNISES không chỉ', 'trình bày khoa học.' ),
			'second'  => array( 'VNISES biến khoa học', 'thành một không gian', 'để khám phá.' ),
		),
	);
}

/* =============================================================================
 * 3. SHORTCODE REGISTRATION
 * ========================================================================== */

/**
 * Đăng ký shortcode [vnises_gioithieu].
 */
function vnises_gt_register_shortcode() {
	if ( ! shortcode_exists( 'vnises_gioithieu' ) ) {
		add_shortcode( 'vnises_gioithieu', 'vnises_gt_shortcode' );
	}
}

if ( did_action( 'init' ) ) {
	vnises_gt_register_shortcode();
} else {
	add_action( 'init', 'vnises_gt_register_shortcode' );
}

/**
 * Callback shortcode. Luôn trả về chuỗi (không echo).
 *
 * Thuộc tính hỗ trợ: heading="1|2|3" — cấp heading cao nhất của module (được sanitize).
 *
 * @param array|string $atts    Thuộc tính shortcode.
 * @param string|null  $content Không dùng.
 * @param string       $tag     Tên shortcode.
 * @return string
 */
function vnises_gt_shortcode( $atts = array(), $content = null, $tag = 'vnises_gioithieu' ) {
	$config = vnises_gt_config();
	$atts   = shortcode_atts(
		array( 'heading' => '' ),
		is_array( $atts ) ? $atts : array(),
		$tag
	);

	$level = ( '' !== trim( (string) $atts['heading'] ) )
		? vnises_gt_sanitize_level( $atts['heading'] )
		: vnises_gt_sanitize_level( isset( $config['heading_level'] ) ? $config['heading_level'] : 2 );

	$ctx = array(
		'id'         => vnises_gt_instance_id(),
		'h1'         => $level,
		'h2'         => $level + 1,
		'h3'         => $level + 2,
		'cta_url'    => vnises_gt_cta_url( $config ),
		'cta_label'  => isset( $config['cta_label'] ) ? (string) $config['cta_label'] : 'Bắt đầu khám phá',
		'full_bleed' => ! empty( $config['full_bleed'] ),
		'offset'     => isset( $config['scroll_offset'] ) ? absint( $config['scroll_offset'] ) : 24,
	);

	$output = '';

	if ( vnises_gt_should_print_asset( 'css' ) ) {
		$output .= '<style id="vnises-gt-css">' . vnises_gt_minify_css( vnises_gt_css() ) . '</style>';
	}

	$output .= vnises_gt_render( $ctx, vnises_gt_content() );

	if ( vnises_gt_should_print_asset( 'js' ) ) {
		$output .= '<script id="vnises-gt-js">' . vnises_gt_js() . '</script>';
	}

	// Không để dòng trống: tránh wpautop/builder chèn <p> vào giữa markup, CSS hoặc JS.
	return preg_replace( "/\n\s*\n/", "\n", $output );
}

/* =============================================================================
 * 4. HTML RENDERER
 * ========================================================================== */

/**
 * ID duy nhất cho mỗi instance (an toàn khi shortcode xuất hiện nhiều lần).
 *
 * @return string
 */
function vnises_gt_instance_id() {
	if ( function_exists( 'wp_unique_id' ) ) {
		return wp_unique_id( 'vngt-' );
	}
	static $counter = 0;
	$counter++;
	return 'vngt-' . $counter;
}

/**
 * Thẻ inline được phép trong chuỗi nội dung.
 *
 * @return array
 */
function vnises_gt_inline_tags() {
	return array(
		'em'     => array( 'class' => true ),
		'strong' => array(),
		'i'      => array(),
		'sup'    => array(),
		'sub'    => array(),
		'span'   => array( 'class' => true, 'lang' => true, 'aria-hidden' => true ),
	);
}

/**
 * Lọc chuỗi nội dung có inline markup.
 *
 * @param string $text Chuỗi.
 * @return string
 */
function vnises_gt_k( $text ) {
	return wp_kses( (string) $text, vnises_gt_inline_tags() );
}

/**
 * Số cho thuộc tính SVG (dấu chấm thập phân, tối đa 2 chữ số, không phụ thuộc locale).
 *
 * @param float $value Giá trị.
 * @return string
 */
function vnises_gt_n( $value ) {
	$s = number_format( (float) $value, 2, '.', '' );
	$s = rtrim( rtrim( $s, '0' ), '.' );
	return ( '-0' === $s || '' === $s ) ? '0' : $s;
}

/**
 * Định dạng số kiểu Việt Nam (dấu phẩy thập phân) để hiển thị.
 *
 * @param float $value    Giá trị.
 * @param int   $decimals Số chữ số thập phân.
 * @return string
 */
function vnises_gt_vn( $value, $decimals = 2 ) {
	return number_format( (float) $value, $decimals, ',', '.' );
}

/**
 * Giải phương trình Kepler M = E − e·sin E bằng Newton–Raphson (0 ≤ e < 1).
 *
 * @param float $m Dị thường trung bình (rad).
 * @param float $e Độ lệch tâm.
 * @return float Dị thường tâm sai E (rad).
 */
function vnises_gt_kepler( $m, $e ) {
	$E = $m + $e * sin( $m );
	for ( $i = 0; $i < 20; $i++ ) {
		$d  = ( $E - $e * sin( $E ) - $m ) / ( 1 - $e * cos( $E ) );
		$E -= $d;
		if ( abs( $d ) < 1e-10 ) {
			break;
		}
	}
	return $E;
}

/**
 * Chuỗi path từ danh sách điểm.
 *
 * @param array $pts Danh sách array(x, y).
 * @return string
 */
function vnises_gt_polyline( $pts ) {
	$d = '';
	foreach ( $pts as $i => $p ) {
		$d .= ( 0 === $i ? 'M' : 'L' ) . vnises_gt_n( $p[0] ) . ' ' . vnises_gt_n( $p[1] );
	}
	return $d;
}

/**
 * Thẻ heading với cấp đã được kiểm soát (1–6).
 *
 * @param int    $level Cấp.
 * @param string $inner HTML đã escape.
 * @param string $class Class.
 * @param string $id    ID (tùy chọn).
 * @return string
 */
function vnises_gt_heading( $level, $inner, $class, $id = '' ) {
	$level = max( 1, min( 6, (int) $level ) );
	$attr  = ' class="' . esc_attr( $class ) . '"';
	if ( '' !== $id ) {
		$attr .= ' id="' . esc_attr( $id ) . '"';
	}
	return '<h' . $level . $attr . '>' . $inner . '</h' . $level . '>';
}

/**
 * Eyebrow đánh số của mỗi scene.
 *
 * @param int    $num   Số thứ tự.
 * @param string $label Nhãn.
 * @return string
 */
function vnises_gt_eyebrow( $num, $label ) {
	return '<p class="vngt-eyebrow"><span class="vngt-eyebrow__idx">' . esc_html( sprintf( '%02d', $num ) ) . '<span class="vngt-sr"> / </span><span class="vngt-eyebrow__total" aria-hidden="true">/09</span></span><span class="vngt-eyebrow__rule" aria-hidden="true"></span><span class="vngt-eyebrow__label">' . esc_html( $label ) . '</span></p>';
}

/**
 * Nhãn loại thông tin của một hình (SIMULATION, SƠ ĐỒ...).
 *
 * @param string $type Loại.
 * @param string $note Ghi chú.
 * @return string
 */
function vnises_gt_ftag( $type, $note ) {
	$lang = preg_match( '/^[A-Z ]+$/', $type ) ? ' lang="en"' : '';
	return '<p class="vngt-ftag"><span class="vngt-ftag__type"' . $lang . '>' . esc_html( $type ) . '</span><span class="vngt-ftag__note">' . esc_html( $note ) . '</span></p>';
}

/**
 * Mở một scene.
 *
 * @param array  $ctx Ngữ cảnh.
 * @param string $key Khóa scene (s01…s09).
 * @param string $mod Modifier class.
 * @return string
 */
function vnises_gt_scene_open( $ctx, $key, $mod ) {
	return '<section id="' . esc_attr( $ctx['id'] . '-' . $key ) . '" class="vngt-scene vngt-scene--' . esc_attr( $mod ) . '" aria-labelledby="' . esc_attr( $ctx['id'] . '-' . $key . '-title' ) . '"><div class="vngt-shell">';
}

/**
 * @return string
 */
function vnises_gt_scene_close() {
	return '</div></section>';
}

/**
 * Bộ render chính.
 *
 * @param array $ctx Ngữ cảnh instance.
 * @param array $c   Nội dung.
 * @return string
 */
function vnises_gt_render( $ctx, $c ) {
	$style = '--vngt-scroll-offset:' . (int) $ctx['offset'] . 'px';
	$html  = '<article id="' . esc_attr( $ctx['id'] ) . '" class="vngt-root" lang="vi" data-vngt-root data-vngt-bleed="' . ( $ctx['full_bleed'] ? '1' : '0' ) . '" style="' . esc_attr( $style ) . '" aria-labelledby="' . esc_attr( $ctx['id'] . '-title' ) . '">';
	$html .= vnises_gt_render_opening( $ctx, $c );
	$html .= vnises_gt_render_identity( $ctx, $c['identity'] );
	$html .= vnises_gt_render_nexus( $ctx, $c['nexus'] );
	$html .= vnises_gt_render_method( $ctx, $c['method'] );
	$html .= vnises_gt_render_landscape( $ctx, $c['landscape'] );
	$html .= vnises_gt_render_interaction( $ctx, $c['interaction'] );
	$html .= vnises_gt_render_integrity( $ctx, $c['integrity'] );
	$html .= vnises_gt_render_standards( $ctx, $c['standards'] );
	$html .= vnises_gt_render_manifesto( $ctx, $c['manifesto'] );
	$html .= '</article>';
	return $html;
}

/* ---- Scene 01 — Science made explorable ---------------------------------- */

function vnises_gt_render_opening( $ctx, $all ) {
	$c  = $all['opening'];
	$id = $ctx['id'];

	$html  = vnises_gt_scene_open( $ctx, 's01', 'opening' );
	$html .= '<header class="vngt-mast">';
	$html .= vnises_gt_heading( $ctx['h1'], esc_html( $all['module']['title'] ), 'vngt-mast__title', $id . '-title' );
	$html .= '<p class="vngt-mast__meta" lang="en">' . esc_html( $all['module']['expansion'] ) . '</p>';
	$html .= '</header>';

	$html .= '<div class="vngt-opening__head">';
	$html .= vnises_gt_eyebrow( 1, $c['eyebrow'] );
	$html .= vnises_gt_heading( $ctx['h2'], vnises_gt_k( $c['title'] ), 'vngt-title vngt-title--opening', $id . '-s01-title' );
	$html .= '</div>';

	$html .= '<div class="vngt-trio vngt-trio--opening">';
	foreach ( $c['examples'] as $ex ) {
		$html .= '<div class="vngt-trio__item" data-vngt-reveal>';
		$html .= '<div class="vngt-trio__head">';
		$html .= vnises_gt_heading( $ctx['h3'], esc_html( $ex['label'] ), 'vngt-trio__label' );
		$html .= '<p class="vngt-trio__claim">' . esc_html( $ex['claim'] ) . '</p>';
		$html .= '</div>';
		$html .= '<figure class="vngt-trio__fig">';
		$html .= vnises_gt_ftag( $ex['type'], $ex['note'] );
		if ( 'orbit' === $ex['key'] ) {
			$html .= vnises_gt_svg_hero_orbit( $ctx, $ex['aria'] );
		} elseif ( 'oscillation' === $ex['key'] ) {
			$html .= vnises_gt_svg_oscillation( $ctx, $ex['aria'] );
		} else {
			$html .= vnises_gt_svg_sun_section( $ctx, $ex['aria'] );
		}
		$html .= '<figcaption class="vngt-caption">';
		if ( ! empty( $ex['equation'] ) ) {
			$html .= '<span class="vngt-equation">' . vnises_gt_k( $ex['equation'] ) . '</span>';
		}
		$html .= vnises_gt_k( $ex['caption'] ) . '</figcaption>';
		$html .= '</figure>';
		$html .= '</div>';
	}
	$html .= '</div>';

	$html .= '<div class="vngt-opening__close" data-vngt-reveal>';
	$html .= '<p class="vngt-opening__statement">' . esc_html( $c['closing'] ) . '</p>';
	$html .= '<p class="vngt-triad">';
	foreach ( $c['triad'] as $i => $word ) {
		if ( $i > 0 ) {
			$html .= '<span class="vngt-triad__dot" aria-hidden="true">·</span>';
		}
		$html .= '<span class="vngt-triad__word">' . esc_html( $word ) . '</span>';
	}
	$html .= '</p></div>';

	// Mục lục — liên kết neo thuần HTML, hoạt động không cần JS.
	$keys  = array( 'opening', 'identity', 'nexus', 'method', 'landscape', 'interaction', 'integrity', 'standards', 'manifesto' );
	$html .= '<nav class="vngt-toc" aria-label="' . esc_attr( $all['module']['nav_label'] ) . '">';
	$html .= '<p class="vngt-toc__title">' . esc_html( $all['module']['nav_title'] ) . '</p><ol class="vngt-toc__list">';
	foreach ( $keys as $i => $key ) {
		$num   = sprintf( '%02d', $i + 1 );
		$html .= '<li class="vngt-toc__item"><a class="vngt-toc__link" href="#' . esc_attr( $id . '-s' . $num ) . '"><span class="vngt-toc__num">' . esc_html( $num ) . '</span><span class="vngt-toc__name">' . esc_html( $all[ $key ]['nav'] ) . '</span></a></li>';
	}
	$html .= '</ol></nav>';

	$html .= vnises_gt_scene_close();
	return $html;
}

/* ---- Scene 02 — Identity ------------------------------------------------- */

function vnises_gt_render_identity( $ctx, $c ) {
	$id    = $ctx['id'];
	$html  = vnises_gt_scene_open( $ctx, 's02', 'identity' );
	$html .= vnises_gt_eyebrow( 2, $c['eyebrow'] );
	$html .= '<div class="vngt-identity">';
	$html .= vnises_gt_heading( $ctx['h2'], esc_html( $c['name'] ), 'vngt-wordmark', $id . '-s02-title' );

	$parts = array();
	foreach ( $c['expansion'] as $word ) {
		$parts[] = ( '' !== $word[0] )
			? '<span class="vngt-expansion__initial">' . esc_html( $word[0] ) . '</span>' . esc_html( $word[1] )
			: '<span class="vngt-expansion__joiner">' . esc_html( $word[1] ) . '</span>';
	}
	$html .= '<p class="vngt-expansion" lang="en">' . implode( ' ', $parts ) . '</p>';
	$html .= '<p class="vngt-lead vngt-identity__lead" data-vngt-reveal>' . esc_html( $c['lead'] ) . '</p>';
	$html .= '</div>';

	$html .= '<ul class="vngt-pillars" data-vngt-reveal>';
	foreach ( $c['pillars'] as $i => $p ) {
		$html .= '<li class="vngt-pillars__item">';
		$html .= '<span class="vngt-pillars__num" aria-hidden="true">' . esc_html( sprintf( '%02d', $i + 1 ) ) . '</span>';
		$html .= vnises_gt_svg_pillar_glyph( $p['key'] );
		$html .= vnises_gt_heading( $ctx['h3'], esc_html( $p['name'] ), 'vngt-pillars__name' );
		$html .= '<p class="vngt-pillars__text">' . esc_html( $p['text'] ) . '</p>';
		$html .= '</li>';
	}
	$html .= '</ul>';
	$html .= vnises_gt_scene_close();
	return $html;
}

/* ---- Scene 03 — Nexus ---------------------------------------------------- */

function vnises_gt_render_nexus( $ctx, $c ) {
	$id    = $ctx['id'];
	$html  = vnises_gt_scene_open( $ctx, 's03', 'nexus' );
	$html .= '<div class="vngt-intro">';
	$html .= '<div class="vngt-intro__head">' . vnises_gt_eyebrow( 3, $c['eyebrow'] ) . vnises_gt_heading( $ctx['h2'], vnises_gt_k( $c['title'] ), 'vngt-title', $id . '-s03-title' ) . '</div>';
	$html .= '<div class="vngt-intro__body vngt-prose">';
	foreach ( $c['body'] as $i => $p ) {
		$html .= '<p' . ( 0 === $i ? ' class="vngt-lead"' : '' ) . '>' . esc_html( $p ) . '</p>';
	}
	$html .= '</div></div>';

	$html .= '<div class="vngt-nexus" data-vngt-nexus>';
	$html .= '<div class="vngt-nexus__diagram" data-vngt-reveal>';
	$html .= '<div class="vngt-nexus__stage" role="group" aria-label="' . esc_attr( $c['diagram_label'] ) . '">';
	$html .= vnises_gt_svg_nexus( $c['relations'] );
	$html .= '<span class="vngt-nexus__core" lang="en" aria-hidden="true">' . esc_html( $c['core'] ) . '</span>';

	$base_angles = array( -90, -45, 0, 45 );
	foreach ( $c['relations'] as $i => $rel ) {
		$angles = array( $base_angles[ $i % 4 ], $base_angles[ $i % 4 ] + 180 );
		foreach ( array( 'a', 'b' ) as $side_index => $side ) {
			$rad   = deg2rad( $angles[ $side_index ] );
			$x     = 50 + 36.6667 * cos( $rad );
			$y     = 50 + 36.6667 * sin( $rad );
			$style = '--vngt-x:' . vnises_gt_n( $x ) . '%;--vngt-y:' . vnises_gt_n( $y ) . '%';
			$html .= '<button type="button" class="vngt-nexus__node" data-vngt-node data-pair="' . (int) $i . '" data-side="' . esc_attr( $side ) . '" style="' . esc_attr( $style ) . '" aria-describedby="' . esc_attr( $id . '-rel-' . $i ) . '">' . esc_html( $rel[ $side ] ) . '</button>';
			if ( 'a' === $side ) {
				$html .= '<span class="vngt-nexus__bridge" data-pair="' . (int) $i . '" aria-hidden="true"></span>';
			}
		}
	}
	$html .= '</div>';
	$html .= '<p class="vngt-nexus__ring-note vngt-caption">' . esc_html( $c['ring_note'] ) . '</p>';
	$html .= '</div>';

	$html .= '<div class="vngt-nexus__side" data-vngt-reveal>';
	$html .= '<p class="vngt-nexus__hint" data-vngt-hint hidden>' . esc_html( $c['hint'] ) . '</p>';
	$html .= '<dl class="vngt-nexus__rels">';
	foreach ( $c['relations'] as $i => $rel ) {
		$html .= '<div class="vngt-nexus__rel" id="' . esc_attr( $id . '-rel-' . $i ) . '" data-pair="' . (int) $i . '">';
		$html .= '<dt class="vngt-nexus__rel-name">' . esc_html( $rel['a'] ) . ' <span aria-hidden="true">↔</span><span class="vngt-sr">và</span> ' . esc_html( $rel['b'] ) . '</dt>';
		$html .= '<dd class="vngt-nexus__rel-text">' . esc_html( $rel['text'] ) . '</dd>';
		$html .= '</div>';
	}
	$html .= '</dl>';

	$q     = $c['question'];
	$html .= '<div class="vngt-question">';
	$html .= vnises_gt_heading( $ctx['h3'], esc_html( $q['label'] ), 'vngt-question__label' );
	$html .= '<p class="vngt-question__text">' . esc_html( $q['text'] ) . '</p>';
	$html .= '<ul class="vngt-question__branches">';
	foreach ( $q['branches'] as $b ) {
		$html .= '<li class="vngt-question__branch"><span class="vngt-question__kind">' . esc_html( $b[0] ) . '</span><span class="vngt-question__desc">' . esc_html( $b[1] ) . '</span></li>';
	}
	$html .= '</ul></div>';
	$html .= '</div>';
	$html .= '</div>';

	$html .= vnises_gt_scene_close();
	return $html;
}

/* ---- Scene 04 — Method + orbit lab --------------------------------------- */

function vnises_gt_render_method( $ctx, $c ) {
	$id    = $ctx['id'];
	$lab   = $c['lab'];
	$e0    = 0.5;
	$html  = vnises_gt_scene_open( $ctx, 's04', 'method' );
	$html .= '<div class="vngt-intro">';
	$html .= '<div class="vngt-intro__head">' . vnises_gt_eyebrow( 4, $c['eyebrow'] ) . vnises_gt_heading( $ctx['h2'], esc_html( $c['title'] ), 'vngt-title', $id . '-s04-title' ) . '</div>';
	$html .= '<div class="vngt-intro__body vngt-prose">';
	foreach ( $c['body'] as $i => $p ) {
		$html .= '<p' . ( 0 === $i ? ' class="vngt-lead"' : '' ) . '>' . esc_html( $p ) . '</p>';
	}
	$html .= '</div></div>';

	$html .= '<ol class="vngt-steps" data-vngt-reveal>';
	foreach ( $c['steps'] as $i => $s ) {
		$html .= '<li class="vngt-steps__item"><span class="vngt-steps__num" aria-hidden="true">' . esc_html( sprintf( '%02d', $i + 1 ) ) . '</span><span class="vngt-steps__name">' . esc_html( $s[0] ) . '</span><span class="vngt-steps__desc">' . esc_html( $s[1] ) . '</span></li>';
	}
	$html .= '</ol>';

	$html .= '<div class="vngt-lab" data-vngt-lab data-e0="' . esc_attr( vnises_gt_n( $e0 ) ) . '">';
	$html .= '<div class="vngt-lab__head">' . vnises_gt_ftag( $lab['type'], $lab['note'] ) . vnises_gt_heading( $ctx['h3'], esc_html( $lab['title'] ), 'vngt-lab__title', $id . '-lab-title' ) . '</div>';
	$html .= '<div class="vngt-lab__grid">';
	$html .= '<figure class="vngt-lab__fig">' . vnises_gt_svg_lab( $ctx, $e0, 0, $lab );
	$html .= '<figcaption class="vngt-lab__legend"><ul class="vngt-legend">';
	foreach ( $lab['legend'] as $key => $label ) {
		$html .= '<li class="vngt-legend__item"><span class="vngt-legend__swatch vngt-legend__swatch--' . esc_attr( $key ) . '" aria-hidden="true"></span>' . esc_html( $label ) . '</li>';
	}
	$html .= '</ul></figcaption></figure>';

	$html .= '<div class="vngt-lab__side">';
	$html .= '<div class="vngt-lab__controls" data-vngt-controls hidden>';
	$html .= '<div class="vngt-lab__slider-head"><label class="vngt-lab__label" for="' . esc_attr( $id . '-e' ) . '">' . vnises_gt_k( $lab['slider'] ) . '</label>';
	$html .= '<output class="vngt-lab__value" for="' . esc_attr( $id . '-e' ) . '" aria-live="off" data-vngt-out="e"><i>e</i> = ' . esc_html( vnises_gt_vn( $e0 ) ) . '</output></div>';
	$html .= '<input class="vngt-range" type="range" id="' . esc_attr( $id . '-e' ) . '" min="0" max="0.85" step="0.01" value="' . esc_attr( vnises_gt_n( $e0 ) ) . '" aria-describedby="' . esc_attr( $id . '-e-help' ) . '" data-vngt-range>';
	$html .= '<div class="vngt-lab__scale" aria-hidden="true"><span>' . esc_html( $lab['min_label'] ) . '</span><span>' . esc_html( $lab['max_label'] ) . '</span></div>';
	$html .= '<p class="vngt-lab__help" id="' . esc_attr( $id . '-e-help' ) . '">' . vnises_gt_k( $lab['help'] ) . '</p>';
	$html .= '<div class="vngt-lab__buttons"><button type="button" class="vngt-btn" data-vngt-pin>' . esc_html( $lab['pin'] ) . '</button><button type="button" class="vngt-btn vngt-btn--quiet" data-vngt-reset>' . esc_html( $lab['reset'] ) . '</button></div>';
	$html .= '</div>';
	$html .= '<p class="vngt-lab__nojs" data-vngt-nojs>' . esc_html( $lab['nojs'] ) . '</p>';

	$b_over_a = sqrt( 1 - $e0 * $e0 );
	$html    .= '<dl class="vngt-readout">';
	$html    .= '<div class="vngt-readout__row vngt-readout__row--key"><dt>' . esc_html( $lab['rows']['cur'] ) . '</dt><dd data-vngt-out="cur"><i>e</i> = ' . esc_html( vnises_gt_vn( $e0 ) ) . '</dd></div>';
	$html    .= '<div class="vngt-readout__row vngt-readout__row--key"><dt>' . esc_html( $lab['rows']['ref'] ) . '</dt><dd data-vngt-out="ref"><i>e</i> = ' . esc_html( vnises_gt_vn( 0 ) ) . '</dd></div>';
	$html    .= '<div class="vngt-readout__row"><dt>' . vnises_gt_k( $lab['rows']['ba'] ) . '</dt><dd data-vngt-out="ba">' . esc_html( vnises_gt_vn( $b_over_a ) ) . '</dd></div>';
	$html    .= '<div class="vngt-readout__row"><dt>' . vnises_gt_k( $lab['rows']['rr'] ) . '</dt><dd data-vngt-out="rr">' . esc_html( vnises_gt_vn( ( 1 - $e0 ) / ( 1 + $e0 ) ) ) . '</dd></div>';
	$html    .= '<div class="vngt-readout__row"><dt>' . vnises_gt_k( $lab['rows']['vv'] ) . '</dt><dd data-vngt-out="vv">' . esc_html( vnises_gt_vn( ( 1 + $e0 ) / ( 1 - $e0 ) ) ) . '</dd></div>';
	$html    .= '</dl>';
	$html    .= '<p class="vngt-lab__verify">' . vnises_gt_k( $lab['verify'] ) . '</p>';
	$html    .= '<p class="vngt-lab__model vngt-caption">' . vnises_gt_k( $lab['model'] ) . '</p>';
	$html    .= '</div>';
	$html    .= '</div></div>';

	$html .= vnises_gt_scene_close();
	return $html;
}

/* ---- Scene 05 — Science landscape ---------------------------------------- */

function vnises_gt_render_landscape( $ctx, $c ) {
	$id    = $ctx['id'];
	$html  = vnises_gt_scene_open( $ctx, 's05', 'landscape' );
	$html .= '<div class="vngt-intro">';
	$html .= '<div class="vngt-intro__head">' . vnises_gt_eyebrow( 5, $c['eyebrow'] ) . vnises_gt_heading( $ctx['h2'], esc_html( $c['title'] ), 'vngt-title', $id . '-s05-title' ) . '</div>';
	$html .= '<div class="vngt-intro__body vngt-prose">';
	foreach ( $c['body'] as $i => $p ) {
		$html .= '<p' . ( 0 === $i ? ' class="vngt-lead"' : '' ) . '>' . esc_html( $p ) . '</p>';
	}
	$html .= '</div></div>';

	$html .= '<div class="vngt-map" data-vngt-map>';
	$html .= '<div class="vngt-map__grid">';
	$html .= '<div class="vngt-map__canvas" data-vngt-reveal>' . vnises_gt_svg_landscape( $ctx, $c ) . '<p class="vngt-map__note vngt-caption">' . esc_html( $c['map_note'] ) . '</p></div>';

	$html .= '<div class="vngt-threads" data-vngt-reveal>';
	$html .= vnises_gt_heading( $ctx['h3'], esc_html( $c['threads_label'] ), 'vngt-subhead' );
	$html .= '<p class="vngt-threads__hint vngt-caption" data-vngt-hint hidden>' . esc_html( $c['threads_hint'] ) . '</p>';
	$html .= '<ul class="vngt-threads__list">';
	foreach ( $c['threads'] as $i => $t ) {
		$html .= '<li class="vngt-thread" data-thread="' . (int) $i . '">';
		$html .= '<button type="button" class="vngt-thread__btn" data-vngt-thread="' . (int) $i . '" aria-controls="' . esc_attr( $id . '-thread-' . $i ) . '"><span class="vngt-thread__name">' . esc_html( $t['name'] ) . '</span><span class="vngt-thread__state" aria-hidden="true"></span></button>';
		$html .= '<div class="vngt-thread__body" id="' . esc_attr( $id . '-thread-' . $i ) . '">';
		$html .= '<ol class="vngt-chain">';
		foreach ( $t['chain'] as $dk ) {
			$html .= '<li class="vngt-chain__step">' . esc_html( $c['domains'][ $dk ]['name'] ) . '</li>';
		}
		$html .= '</ol>';
		$html .= '<p class="vngt-thread__text">' . esc_html( $t['text'] ) . '</p>';
		$html .= '</div></li>';
	}
	$html .= '</ul></div>';
	$html .= '</div>';

	$html .= '<div class="vngt-domains-wrap" data-vngt-reveal>' . vnises_gt_heading( $ctx['h3'], esc_html( $c['domains_label'] ), 'vngt-subhead' );
	$html .= '<ol class="vngt-domains">';
	$n = 0;
	foreach ( $c['domains'] as $key => $d ) {
		$n++;
		$html .= '<li class="vngt-domain" data-domain="' . esc_attr( $key ) . '"><span class="vngt-domain__num" aria-hidden="true">' . esc_html( sprintf( '%02d', $n ) ) . '</span><span class="vngt-domain__order" aria-hidden="true" data-vngt-order></span>';
		$html .= '<p class="vngt-domain__name">' . esc_html( $d['name'] ) . '</p>';
		$html .= '<p class="vngt-domain__text">' . esc_html( $d['text'] ) . '</p></li>';
	}
	$html .= '</ol></div>';
	$html .= '</div>';

	$html .= '<div class="vngt-layers" data-vngt-reveal>';
	$html .= vnises_gt_heading( $ctx['h3'], esc_html( $c['layers_label'] ), 'vngt-subhead' );
	$html .= '<ol class="vngt-layers__list">';
	foreach ( $c['layers'] as $i => $l ) {
		$html .= '<li class="vngt-layers__item" style="--vngt-depth:' . (int) $i . '"><span class="vngt-layers__name">' . esc_html( $l[0] ) . '</span><span class="vngt-layers__desc">' . esc_html( $l[1] ) . '</span></li>';
	}
	$html .= '</ol></div>';

	$html .= vnises_gt_scene_close();
	return $html;
}

/* ---- Scene 06 — Interaction is core -------------------------------------- */

function vnises_gt_render_interaction( $ctx, $c ) {
	$id    = $ctx['id'];
	$st    = $c['station'];
	$html  = vnises_gt_scene_open( $ctx, 's06', 'interaction' );
	$html .= '<div class="vngt-interaction__head">';
	$html .= vnises_gt_eyebrow( 6, $c['eyebrow'] );
	$html .= vnises_gt_heading( $ctx['h2'], vnises_gt_k( $c['title'] ), 'vngt-title vngt-title--wide', $id . '-s06-title' );
	$html .= '<p class="vngt-lead vngt-interaction__lead">' . esc_html( $c['body'] ) . '</p>';
	$html .= '</div>';

	$html .= '<div class="vngt-loop" data-vngt-reveal>';
	$html .= vnises_gt_heading( $ctx['h3'], esc_html( $c['loop_label'] ), 'vngt-subhead' );
	$html .= '<ol class="vngt-loop__list">';
	foreach ( $c['loop'] as $i => $step ) {
		$html .= '<li class="vngt-loop__item"><span class="vngt-loop__num" aria-hidden="true">' . (int) ( $i + 1 ) . '</span><span class="vngt-loop__name">' . esc_html( $step ) . '</span></li>';
	}
	$html .= '</ol>';
	$html .= '<p class="vngt-loop__note"><span class="vngt-loop__return" aria-hidden="true">↺</span>' . esc_html( $c['loop_note'] ) . '</p>';
	$html .= '</div>';

	$html .= '<div class="vngt-station">';
	$html .= '<div class="vngt-station__intro" data-vngt-reveal>';
	$html .= vnises_gt_heading( $ctx['h3'], esc_html( $st['title'] ), 'vngt-station__title' );
	$html .= '<p class="vngt-station__body">' . esc_html( $st['body'] ) . '</p>';
	$html .= '<p class="vngt-notice"><span class="vngt-notice__tag">' . esc_html( $st['notice_tag'] ) . '</span><span class="vngt-notice__text">' . esc_html( $st['notice'] ) . '</span></p>';
	$html .= '</div>';

	$html .= '<div class="vngt-trio vngt-trio--station">';
	foreach ( $st['components'] as $comp ) {
		$html .= '<div class="vngt-trio__item" data-vngt-reveal>';
		$html .= '<div class="vngt-trio__head">';
		$html .= '<p class="vngt-trio__micro">' . esc_html( $comp['micro'] ) . '</p>';
		$html .= vnises_gt_heading( $ctx['h3'] + 1, esc_html( $comp['title'] ), 'vngt-trio__label vngt-trio__label--plain' );
		$html .= '<p class="vngt-trio__text">' . esc_html( $comp['text'] ) . '</p>';
		$html .= '</div>';
		$html .= '<figure class="vngt-trio__fig">' . vnises_gt_ftag( $comp['type'], $comp['note'] );
		if ( 'iss' === $comp['key'] ) {
			$html .= vnises_gt_svg_iss( $ctx, $comp['aria'] );
		} elseif ( 'track' === $comp['key'] ) {
			$html .= vnises_gt_svg_groundtrack( $ctx, $comp['aria'] );
		} else {
			$html .= vnises_gt_svg_sun_grid( $ctx, $comp['aria'] );
		}
		$html .= '<figcaption class="vngt-caption">' . esc_html( $comp['caption'] ) . '</figcaption></figure>';
		$html .= '</div>';
	}
	$html .= '</div></div>';

	$html .= vnises_gt_scene_close();
	return $html;
}

/* ---- Scene 07 — Scientific integrity ------------------------------------- */

function vnises_gt_render_integrity( $ctx, $c ) {
	$id    = $ctx['id'];
	$html  = vnises_gt_scene_open( $ctx, 's07', 'integrity' );
	$html .= '<div class="vngt-intro">';
	$html .= '<div class="vngt-intro__head">' . vnises_gt_eyebrow( 7, $c['eyebrow'] ) . vnises_gt_heading( $ctx['h2'], esc_html( $c['title'] ), 'vngt-title vngt-title--caps', $id . '-s07-title' ) . '</div>';
	$html .= '<div class="vngt-intro__body vngt-prose"><p class="vngt-lead">' . esc_html( $c['body'] ) . '</p></div>';
	$html .= '</div>';

	$html .= '<div class="vngt-integrity">';
	$html .= '<figure class="vngt-integrity__fig">' . vnises_gt_ftag( $c['chart']['type'], $c['chart']['note'] ) . vnises_gt_svg_integrity( $ctx, $c ) . '<figcaption class="vngt-caption">' . esc_html( $c['chart']['caption'] ) . '</figcaption></figure>';

	$html .= '<div class="vngt-integrity__taxo">';
	$html .= vnises_gt_heading( $ctx['h3'], esc_html( $c['taxo_label'] ), 'vngt-subhead' );
	$html .= '<dl class="vngt-taxo">';
	foreach ( $c['taxo'] as $i => $t ) {
		$html .= '<div class="vngt-taxo__row">';
		$html .= '<dt class="vngt-taxo__term"><span class="vngt-taxo__num" aria-hidden="true">' . (int) ( $i + 1 ) . '</span><span class="vngt-taxo__glyph vngt-taxo__glyph--' . esc_attr( $t['key'] ) . '" aria-hidden="true"></span><span class="vngt-taxo__en" lang="en">' . esc_html( $t['en'] ) . '</span><span class="vngt-taxo__vi">' . esc_html( $t['vi'] ) . '</span></dt>';
		$html .= '<dd class="vngt-taxo__def">' . esc_html( $t['text'] ) . '</dd>';
		$html .= '</div>';
	}
	$html .= '</dl>';
	$html .= '<p class="vngt-integrity__nuance">' . esc_html( $c['nuance'] ) . '</p>';
	$html .= '</div>';
	$html .= '</div>';

	$p     = $c['prov'];
	$html .= '<div class="vngt-prov">';
	$html .= '<div class="vngt-prov__head">' . vnises_gt_heading( $ctx['h3'], esc_html( $p['title'] ), 'vngt-subhead' ) . '<p class="vngt-prov__intro">' . esc_html( $p['intro'] ) . '</p></div>';
	$html .= '<div class="vngt-prov__cols" aria-hidden="true"><span>' . esc_html( $p['head'][0] ) . '</span><span>' . esc_html( $p['head'][1] ) . '</span><span>' . esc_html( $p['head'][2] ) . '</span></div>';
	$html .= '<dl class="vngt-prov__list">';
	foreach ( $p['rows'] as $row ) {
		$html .= '<div class="vngt-prov__row">';
		$html .= '<dt class="vngt-prov__field"><span class="vngt-prov__en" lang="en">' . esc_html( $row[0] ) . '</span><span class="vngt-prov__vi">' . esc_html( $row[1] ) . '</span></dt>';
		$html .= '<dd class="vngt-prov__need"><span class="vngt-prov__label">' . esc_html( $p['head'][1] ) . ': </span>' . esc_html( $row[2] ) . '</dd>';
		$html .= '<dd class="vngt-prov__example"><span class="vngt-prov__label">' . esc_html( $p['head'][2] ) . ': </span>' . esc_html( $row[3] ) . '</dd>';
		$html .= '</div>';
	}
	$html .= '</dl></div>';

	$html .= '<div class="vngt-integrity__close">';
	$html .= '<p class="vngt-pull">' . esc_html( $c['statement'] ) . '</p>';
	$html .= '<p class="vngt-integrity__figs vngt-caption">' . esc_html( $c['figures_note'] ) . '</p>';
	$html .= '</div>';

	$html .= vnises_gt_scene_close();
	return $html;
}

/* ---- Scene 08 — International quality direction -------------------------- */

function vnises_gt_render_standards( $ctx, $c ) {
	$id    = $ctx['id'];
	$html  = vnises_gt_scene_open( $ctx, 's08', 'standards' );
	$html .= '<div class="vngt-intro">';
	$html .= '<div class="vngt-intro__head">' . vnises_gt_eyebrow( 8, $c['eyebrow'] ) . vnises_gt_heading( $ctx['h2'], esc_html( $c['title'] ), 'vngt-title', $id . '-s08-title' );
	$html .= '<p class="vngt-status"><span class="vngt-status__tag">' . esc_html( $c['status'] ) . '</span><span class="vngt-status__note">' . esc_html( $c['status_note'] ) . '</span></p></div>';
	$html .= '<div class="vngt-intro__body vngt-prose"><p class="vngt-lead">' . esc_html( $c['body'] ) . '</p></div>';
	$html .= '</div>';

	$html .= '<div class="vngt-std">';
	$html .= '<div class="vngt-std__cols" aria-hidden="true"><span></span><span>' . esc_html( $c['col_head'][0] ) . '</span><span>' . esc_html( $c['col_head'][1] ) . '</span></div>';
	$html .= '<ol class="vngt-std__list">';
	foreach ( $c['pillars'] as $i => $p ) {
		$html .= '<li class="vngt-std__item"><span class="vngt-std__num" aria-hidden="true">' . esc_html( sprintf( '%02d', $i + 1 ) ) . '</span>';
		$html .= vnises_gt_heading( $ctx['h3'], esc_html( $p[0] ), 'vngt-std__name' );
		$html .= '<p class="vngt-std__text">' . esc_html( $p[1] ) . '</p></li>';
	}
	$html .= '</ol></div>';

	$html .= vnises_gt_scene_close();
	return $html;
}

/* ---- Scene 09 — Manifesto ------------------------------------------------ */

function vnises_gt_render_manifesto( $ctx, $c ) {
	$id    = $ctx['id'];
	$html  = vnises_gt_scene_open( $ctx, 's09', 'manifesto' );
	$html .= vnises_gt_eyebrow( 9, $c['eyebrow'] );

	$inner = '<span class="vngt-manifesto__first">';
	foreach ( $c['first'] as $i => $line ) {
		$inner .= ( $i > 0 ? ' ' : '' ) . '<span class="vngt-manifesto__line">' . esc_html( $line ) . '</span>';
	}
	$inner .= '</span> <span class="vngt-manifesto__second">';
	foreach ( $c['second'] as $i => $line ) {
		$inner .= ( $i > 0 ? ' ' : '' ) . '<span class="vngt-manifesto__line">' . esc_html( $line ) . '</span>';
	}
	$inner .= '</span>';

	$html .= '<div class="vngt-manifesto" data-vngt-reveal>';
	$html .= vnises_gt_heading( $ctx['h2'], $inner, 'vngt-manifesto__text', $id . '-s09-title' );
	$html .= '<p class="vngt-manifesto__action"><a class="vngt-cta" href="' . esc_url( $ctx['cta_url'] ) . '">' . esc_html( $ctx['cta_label'] ) . '<span class="vngt-cta__arrow" aria-hidden="true">→</span></a></p>';
	$html .= '</div>';

	$html .= vnises_gt_scene_close();
	return $html;
}

/* ---- SVG: scientific figures --------------------------------------------- */

/**
 * Hình 01a — quỹ đạo Kepler với hai vùng diện tích quét bằng nhau (trạng thái tĩnh do server render;
 * JS chuyển thành một vùng quét chạy theo phương trình Kepler nếu người dùng không yêu cầu giảm chuyển động).
 */
function vnises_gt_svg_hero_orbit( $ctx, $aria ) {
	$a   = 120;
	$e   = 0.6;
	$cx  = 150;
	$cy  = 112;
	$b   = $a * sqrt( 1 - $e * $e );
	$fx  = $cx + $a * $e;
	$dm  = 2 * M_PI * 0.08; // Δt = 8% chu kỳ.
	$pos = function ( $E ) use ( $cx, $cy, $a, $b ) {
		return array( $cx + $a * cos( $E ), $cy - $b * sin( $E ) );
	};
	$wedge = function ( $m1, $m2 ) use ( $e, $a, $b, $fx, $cy, $pos ) {
		$E1 = vnises_gt_kepler( $m1, $e );
		$E2 = vnises_gt_kepler( $m2, $e );
		$p1 = $pos( $E1 );
		$p2 = $pos( $E2 );
		return 'M' . vnises_gt_n( $fx ) . ' ' . vnises_gt_n( $cy ) . 'L' . vnises_gt_n( $p1[0] ) . ' ' . vnises_gt_n( $p1[1] ) . 'A' . vnises_gt_n( $a ) . ' ' . vnises_gt_n( $b ) . ' 0 ' . ( ( $E2 - $E1 ) > M_PI ? '1' : '0' ) . ' 0 ' . vnises_gt_n( $p2[0] ) . ' ' . vnises_gt_n( $p2[1] ) . 'Z';
	};
	$body  = $pos( vnises_gt_kepler( $dm / 2, $e ) );
	$orbit = 'M' . vnises_gt_n( $cx + $a ) . ' ' . $cy . 'A' . $a . ' ' . vnises_gt_n( $b ) . ' 0 1 0 ' . vnises_gt_n( $cx - $a ) . ' ' . $cy . 'A' . $a . ' ' . vnises_gt_n( $b ) . ' 0 1 0 ' . vnises_gt_n( $cx + $a ) . ' ' . $cy . 'Z';

	$s  = '<svg class="vngt-svg vngt-svg--hero-orbit" viewBox="0 0 340 230" role="img" aria-label="' . esc_attr( $aria ) . '" focusable="false" data-vngt-hero-orbit data-a="' . $a . '" data-e="' . $e . '" data-cx="' . $cx . '" data-cy="' . $cy . '" data-dm="' . vnises_gt_n( 0.08 ) . '">';
	$s .= '<path class="vngt-g vngt-g--faint vngt-g--dash" d="M14 ' . $cy . 'H326"/>';
	$s .= '<path class="vngt-g vngt-g--main" data-vngt-draw d="' . $orbit . '"/>';
	$s .= '<path class="vngt-g-wedge" data-vngt-wedge="a" d="' . $wedge( -$dm / 2, $dm / 2 ) . '"/>';
	$s .= '<path class="vngt-g-wedge" data-vngt-wedge="b" d="' . $wedge( M_PI - $dm / 2, M_PI + $dm / 2 ) . '"/>';
	$s .= '<line class="vngt-g vngt-g--radius" data-vngt-radius x1="' . vnises_gt_n( $fx ) . '" y1="' . $cy . '" x2="' . vnises_gt_n( $body[0] ) . '" y2="' . vnises_gt_n( $body[1] ) . '"/>';
	$s .= '<circle class="vngt-g-focus" cx="' . vnises_gt_n( $fx ) . '" cy="' . $cy . '" r="6"/>';
	$s .= '<circle class="vngt-g-body" data-vngt-body cx="' . vnises_gt_n( $body[0] ) . '" cy="' . vnises_gt_n( $body[1] ) . '" r="4.5"/>';
	$s .= '<text class="vngt-g-label" x="' . vnises_gt_n( $fx - 9 ) . '" y="' . ( $cy + 20 ) . '" text-anchor="end"><tspan font-style="italic">F</tspan></text>';
	$s .= '<text class="vngt-g-label" x="276" y="142">cận điểm</text>';
	$s .= '<text class="vngt-g-label" x="36" y="144">viễn điểm</text>';
	$s .= '<text class="vngt-g-label vngt-g-label--accent" data-vngt-static x="278" y="102">Δt</text>';
	$s .= '<text class="vngt-g-label vngt-g-label--accent" data-vngt-static x="24" y="104" text-anchor="end">Δt</text>';
	$s .= '</svg>';
	return $s;
}

/**
 * Hình 01b — dao động tắt dần x(t) = A·e^(−γt)·cos(ωt), tính trên server.
 */
function vnises_gt_svg_oscillation( $ctx, $aria ) {
	$x0    = 34;
	$x1    = 324;
	$y0    = 118;
	$amp   = 82;
	$tmax  = 10;
	$gamma = 0.26;
	$omega = 2 * M_PI / 1.55;
	$curve = array();
	$up    = array();
	$down  = array();
	for ( $i = 0; $i <= 240; $i++ ) {
		$t       = $tmax * $i / 240;
		$x       = $x0 + ( $x1 - $x0 ) * $t / $tmax;
		$env     = exp( -$gamma * $t );
		$curve[] = array( $x, $y0 - $amp * $env * cos( $omega * $t ) );
		if ( 0 === $i % 6 ) {
			$up[]   = array( $x, $y0 - $amp * $env );
			$down[] = array( $x, $y0 + $amp * $env );
		}
	}
	$s  = '<svg class="vngt-svg" viewBox="0 0 340 230" role="img" aria-label="' . esc_attr( $aria ) . '" focusable="false">';
	$s .= '<path class="vngt-g vngt-g--faint" d="M' . $x0 . ' ' . $y0 . 'H' . ( $x1 + 4 ) . 'M' . $x0 . ' 22V214"/>';
	$s .= '<path class="vngt-g vngt-g--axis" d="M' . ( $x1 + 4 ) . ' ' . $y0 . 'l-6 -3.5v7z"/>';
	$s .= '<path class="vngt-g vngt-g--env vngt-g--dash" d="' . vnises_gt_polyline( $up ) . '"/>';
	$s .= '<path class="vngt-g vngt-g--env vngt-g--dash" d="' . vnises_gt_polyline( $down ) . '"/>';
	$s .= '<path class="vngt-g vngt-g--main" data-vngt-draw d="' . vnises_gt_polyline( $curve ) . '"/>';
	$s .= '<text class="vngt-g-label" x="' . ( $x1 + 2 ) . '" y="' . ( $y0 + 22 ) . '" text-anchor="end" font-style="italic">t</text>';
	$s .= '<text class="vngt-g-label" x="' . ( $x0 + 8 ) . '" y="30" font-style="italic">x</text>';
	$s .= '<text class="vngt-g-label" x="140" y="' . vnises_gt_n( $y0 - $amp * exp( -$gamma * 3.7 ) - 10 ) . '">đường bao</text>';
	$s .= '</svg>';
	return $s;
}

/**
 * Hình 01c — Mặt Trời: tối rìa + mặt cắt cấu trúc (lõi ≈ 0,25 R, đáy vùng đối lưu ≈ 0,7 R).
 */
function vnises_gt_svg_sun_section( $ctx, $aria ) {
	$gid = $ctx['id'] . '-sun1';
	$cx  = 140;
	$cy  = 106;
	$r   = 92;
	$rc  = 0.25 * $r;
	$rr  = 0.7 * $r;
	$q   = function ( $rad ) use ( $cx, $cy ) {
		return 'M' . $cx . ' ' . $cy . 'L' . $cx . ' ' . vnises_gt_n( $cy - $rad ) . 'A' . vnises_gt_n( $rad ) . ' ' . vnises_gt_n( $rad ) . ' 0 0 1 ' . vnises_gt_n( $cx + $rad ) . ' ' . $cy . 'Z';
	};
	$pt  = function ( $rad ) use ( $cx, $cy ) {
		return array( $cx + $rad * cos( deg2rad( -45 ) ), $cy + $rad * sin( deg2rad( -45 ) ) );
	};
	$labels = array(
		array( $pt( 0.85 * $r ), 'vùng đối lưu', 30 ),
		array( $pt( 0.47 * $r ), 'vùng bức xạ', 54 ),
		array( $pt( 0.12 * $r ), 'lõi', 78 ),
	);
	$s  = '<svg class="vngt-svg" viewBox="0 0 340 230" role="img" aria-label="' . esc_attr( $aria ) . '" focusable="false">';
	$s .= '<defs><radialGradient id="' . esc_attr( $gid ) . '" cx="50%" cy="50%" r="50%"><stop offset="0" stop-color="#f4e6c4"/><stop offset=".55" stop-color="#e8c784"/><stop offset=".85" stop-color="#d4a457"/><stop offset="1" stop-color="#a8742f"/></radialGradient></defs>';
	$s .= '<circle cx="' . $cx . '" cy="' . $cy . '" r="' . $r . '" fill="url(#' . esc_attr( $gid ) . ')"/>';
	$s .= '<path class="vngt-g-cut vngt-g-cut--conv" d="' . $q( $r ) . '"/>';
	$s .= '<path class="vngt-g-cut vngt-g-cut--rad" d="' . $q( $rr ) . '"/>';
	$s .= '<path class="vngt-g-cut vngt-g-cut--core" d="' . $q( $rc ) . '"/>';
	foreach ( $labels as $l ) {
		$s .= '<path class="vngt-g vngt-g--leader" d="M' . vnises_gt_n( $l[0][0] ) . ' ' . vnises_gt_n( $l[0][1] ) . 'L246 ' . ( $l[2] - 4 ) . 'H252"/>';
		$s .= '<circle class="vngt-g-dot" cx="' . vnises_gt_n( $l[0][0] ) . '" cy="' . vnises_gt_n( $l[0][1] ) . '" r="2"/>';
		$s .= '<text class="vngt-g-label" x="256" y="' . $l[2] . '">' . esc_html( $l[1] ) . '</text>';
	}
	$limb = array( $cx + $r * cos( deg2rad( 145 ) ), $cy + $r * sin( deg2rad( 145 ) ) );
	$s   .= '<path class="vngt-g vngt-g--leader" d="M' . vnises_gt_n( $limb[0] ) . ' ' . vnises_gt_n( $limb[1] ) . 'L22 212"/>';
	$s   .= '<circle class="vngt-g-dot" cx="' . vnises_gt_n( $limb[0] ) . '" cy="' . vnises_gt_n( $limb[1] ) . '" r="2"/>';
	$s   .= '<text class="vngt-g-label" x="26" y="224">rìa đĩa tối hơn tâm</text>';
	$s   .= '</svg>';
	return $s;
}

/**
 * Glyph nhỏ cho bốn trụ cột (trang trí có nghĩa, aria-hidden vì tên trụ cột đã là văn bản).
 */
function vnises_gt_svg_pillar_glyph( $key ) {
	$s = '<svg class="vngt-pillars__glyph" viewBox="0 0 48 48" aria-hidden="true" focusable="false">';
	switch ( $key ) {
		case 'viz':
			$s .= '<path class="vngt-g vngt-g--faint" d="M6 42H44M6 42V6"/><path class="vngt-g vngt-g--main" d="M8 38C16 36 18 14 26 14S36 30 44 28"/>';
			break;
		case 'sim':
			$s .= '<ellipse class="vngt-g vngt-g--faint vngt-g--dash" cx="24" cy="24" rx="19" ry="12"/><circle class="vngt-g-focus" cx="33" cy="24" r="3"/><circle class="vngt-g-body" cx="13" cy="15" r="2.5"/>';
			break;
		case 'int':
			$s .= '<path class="vngt-g vngt-g--faint" d="M6 30H42"/><path class="vngt-g vngt-g--accent" d="M6 30H28"/><circle class="vngt-g-knob" cx="28" cy="30" r="5"/><path class="vngt-g vngt-g--faint" d="M10 16H38" stroke-dasharray="2 4"/>';
			break;
		default:
			$s .= '<path class="vngt-g vngt-g--faint" d="M6 42H44"/><path class="vngt-g vngt-g--main" d="M12 22V34M22 14V26M32 20V30M40 10V22"/><circle class="vngt-g-dot2" cx="12" cy="28" r="2.5"/><circle class="vngt-g-dot2" cx="22" cy="20" r="2.5"/><circle class="vngt-g-dot2" cx="32" cy="25" r="2.5"/><circle class="vngt-g-dot2" cx="40" cy="16" r="2.5"/>';
	}
	$s .= '</svg>';
	return $s;
}

/**
 * Sơ đồ Nexus (SVG trang trí; nhãn và tương tác là các <button> HTML đặt chồng lên).
 */
function vnises_gt_svg_nexus( $relations ) {
	$R           = 220;
	$base_angles = array( -90, -45, 0, 45 );
	$s           = '<svg class="vngt-nexus__svg" viewBox="-300 -300 600 600" aria-hidden="true" focusable="false">';
	$s          .= '<circle class="vngt-nexus__ring" r="276"/>';
	for ( $k = 0; $k < 8; $k++ ) {
		$ang = deg2rad( -67.5 + 45 * $k );
		$s  .= '<path class="vngt-nexus__tick" d="M' . vnises_gt_n( 268 * cos( $ang ) ) . ' ' . vnises_gt_n( 268 * sin( $ang ) ) . 'L' . vnises_gt_n( 284 * cos( $ang ) ) . ' ' . vnises_gt_n( 284 * sin( $ang ) ) . '"/>';
	}
	$s .= '<circle class="vngt-nexus__orbit" r="' . $R . '"/>';
	foreach ( $relations as $i => $rel ) {
		$a1 = deg2rad( $base_angles[ $i % 4 ] );
		$a2 = $a1 + M_PI;
		$s .= '<path class="vngt-nexus__link" data-vngt-link="' . (int) $i . '" data-vngt-draw d="M' . vnises_gt_n( $R * cos( $a1 ) ) . ' ' . vnises_gt_n( $R * sin( $a1 ) ) . 'L' . vnises_gt_n( $R * cos( $a2 ) ) . ' ' . vnises_gt_n( $R * sin( $a2 ) ) . '"/>';
	}
	$s .= '<circle class="vngt-nexus__hub" r="58"/>';
	$s .= '</svg>';
	return $s;
}

/**
 * Hình 04 — phòng thí nghiệm độ lệch tâm (trạng thái ban đầu render trên server, JS cập nhật).
 *
 * Hệ tọa độ: tiêu điểm F cố định (300, 170), bán trục lớn a = 150. Tâm elip C = F − (a·e, 0);
 * cận điểm nằm bên phải F, viễn điểm bên trái. Vị trí theo dị thường tâm sai E:
 * P(E) = C + (a·cos E, −b·sin E).
 */
function vnises_gt_svg_lab( $ctx, $e, $e_ref, $lab ) {
	$id  = $ctx['id'];
	$a   = 150;
	$fx  = 300;
	$fy  = 170;
	$geo = function ( $ecc ) use ( $a, $fx ) {
		return array( 'c' => $a * $ecc, 'b' => $a * sqrt( 1 - $ecc * $ecc ), 'cx' => $fx - $a * $ecc );
	};
	$g   = $geo( $e );
	$gr  = $geo( $e_ref );
	$mid = $id . '-arrow';

	$s  = '<svg class="vngt-svg vngt-svg--lab" viewBox="0 0 480 340" role="img" aria-labelledby="' . esc_attr( $id . '-lab-title' ) . '" aria-describedby="' . esc_attr( $id . '-lab-desc' ) . '" focusable="false" data-vngt-lab-svg data-a="' . $a . '" data-fx="' . $fx . '" data-fy="' . $fy . '">';
	$s .= '<desc id="' . esc_attr( $id . '-lab-desc' ) . '">' . esc_html( $lab['aria'] ) . '</desc>';
	$s .= '<defs><marker id="' . esc_attr( $mid ) . '" viewBox="0 0 10 10" refX="8" refY="5" markerWidth="7" markerHeight="7" orient="auto-start-reverse"><path class="vngt-g-arrowhead" d="M0 1L9 5L0 9z"/></marker></defs>';
	$s .= '<path class="vngt-g vngt-g--faint vngt-g--dash" d="M8 ' . $fy . 'H472"/>';
	$s .= '<ellipse class="vngt-g vngt-g--ref" data-vngt-ref cx="' . vnises_gt_n( $gr['cx'] ) . '" cy="' . $fy . '" rx="' . $a . '" ry="' . vnises_gt_n( $gr['b'] ) . '"/>';
	$s .= '<ellipse class="vngt-g vngt-g--main" data-vngt-cur cx="' . vnises_gt_n( $g['cx'] ) . '" cy="' . $fy . '" rx="' . $a . '" ry="' . vnises_gt_n( $g['b'] ) . '"/>';
	$s .= '<g data-vngt-ticks>';
	for ( $k = 0; $k < 12; $k++ ) {
		$E  = vnises_gt_kepler( 2 * M_PI * $k / 12, $e );
		$s .= '<circle class="vngt-g-tick" cx="' . vnises_gt_n( $g['cx'] + $a * cos( $E ) ) . '" cy="' . vnises_gt_n( $fy - $g['b'] * sin( $E ) ) . '" r="3.2"/>';
	}
	$s   .= '</g>';
	$f2x  = $fx - 2 * $g['c'];
	$peri = $g['cx'] + $a;
	$apo  = $g['cx'] - $a;
	$s   .= '<circle class="vngt-g-focus2" data-vngt-f2 cx="' . vnises_gt_n( $f2x ) . '" cy="' . $fy . '" r="4"/>';
	$s   .= '<text class="vngt-g-label vngt-g-label--lab" data-vngt-f2-label x="' . vnises_gt_n( $f2x ) . '" y="' . ( $fy + 26 ) . '" text-anchor="middle" font-style="italic">F′</text>';
	$s   .= '<circle class="vngt-g-focus" cx="' . $fx . '" cy="' . $fy . '" r="7"/>';
	$s   .= '<text class="vngt-g-label vngt-g-label--lab" x="' . $fx . '" y="' . ( $fy + 28 ) . '" text-anchor="middle" font-style="italic">F</text>';
	$s   .= '<text class="vngt-g-label vngt-g-label--lab" data-vngt-peri-label x="' . vnises_gt_n( $peri - 8 ) . '" y="' . ( $fy - 12 ) . '" text-anchor="end">' . esc_html( $lab['labels']['peri'] ) . '</text>';
	$s   .= '<text class="vngt-g-label vngt-g-label--lab" data-vngt-apo-label x="' . vnises_gt_n( $apo + 8 ) . '" y="' . ( $fy - 12 ) . '">' . esc_html( $lab['labels']['apo'] ) . '</text>';
	// Vật tại cận điểm (E = 0); vận tốc tiếp tuyến hướng lên, độ dài ∝ √(2a/r − 1) (vis-viva, chuẩn hóa).
	$r_p  = $a * ( 1 - $e );
	$vlen = 26 * sqrt( 2 * $a / $r_p - 1 );
	$s   .= '<line class="vngt-g vngt-g--vel" data-vngt-vel x1="' . vnises_gt_n( $peri ) . '" y1="' . $fy . '" x2="' . vnises_gt_n( $peri ) . '" y2="' . vnises_gt_n( $fy - $vlen ) . '" marker-end="url(#' . esc_attr( $mid ) . ')"/>';
	$s   .= '<circle class="vngt-g-body" data-vngt-body cx="' . vnises_gt_n( $peri ) . '" cy="' . $fy . '" r="6"/>';
	$s   .= '</svg>';
	return $s;
}

/**
 * Bản đồ lĩnh vực (desktop/tablet). Trên mobile được thay bằng danh sách tuyến tính.
 */
function vnises_gt_svg_landscape( $ctx, $c ) {
	$d   = $c['domains'];
	$mid = $ctx['id'] . '-tarrow';
	$s   = '<svg class="vngt-map__svg" viewBox="0 0 1000 560" aria-hidden="true" focusable="false">';
	$s  .= '<defs><marker id="' . esc_attr( $mid ) . '" viewBox="0 0 10 10" refX="7" refY="5" markerWidth="9" markerHeight="9" orient="auto-start-reverse"><path class="vngt-g-arrowhead vngt-g-arrowhead--accent" d="M0 1L9 5L0 9z"/></marker></defs>';
	foreach ( $c['edges'] as $edge ) {
		$p1  = $d[ $edge[0] ];
		$p2  = $d[ $edge[1] ];
		$s  .= '<path class="vngt-map__edge" data-vngt-draw d="M' . $p1['x'] . ' ' . $p1['y'] . 'L' . $p2['x'] . ' ' . $p2['y'] . '"/>';
	}
	foreach ( $c['threads'] as $i => $t ) {
		$pts = array();
		foreach ( $t['chain'] as $k ) {
			$pts[] = array( $d[ $k ]['x'], $d[ $k ]['y'] );
		}
		$s .= '<path class="vngt-map__thread" data-vngt-thread-path="' . (int) $i . '" marker-end="url(#' . esc_attr( $mid ) . ')" d="' . vnises_gt_smooth_path( $pts, 18 ) . '"/>';
	}
	foreach ( $d as $key => $node ) {
		$ly  = ( 'top' === $node['lp'] ) ? $node['y'] - 22 : $node['y'] + 38;
		$s  .= '<g class="vngt-map__node" data-vngt-map-node="' . esc_attr( $key ) . '">';
		$s  .= '<circle class="vngt-map__halo" cx="' . $node['x'] . '" cy="' . $node['y'] . '" r="18"/>';
		$s  .= '<circle class="vngt-map__dot" cx="' . $node['x'] . '" cy="' . $node['y'] . '" r="7"/>';
		$s  .= '<text class="vngt-map__label" x="' . $node['x'] . '" y="' . $ly . '" text-anchor="middle">' . esc_html( $node['name'] ) . '</text>';
		$s  .= '</g>';
	}
	$s .= '</svg>';
	return $s;
}

/**
 * Đường cong mượt (Catmull–Rom → Bézier) đi qua các điểm; rút ngắn đoạn cuối để mũi tên không bị nút che.
 *
 * @param array $pts  Điểm.
 * @param float $trim Độ rút ngắn ở hai đầu.
 * @return string
 */
function vnises_gt_smooth_path( $pts, $trim ) {
	$n = count( $pts );
	if ( $n < 2 ) {
		return '';
	}
	$first    = $pts[0];
	$dx       = $pts[1][0] - $first[0];
	$dy       = $pts[1][1] - $first[1];
	$len      = max( 1e-6, sqrt( $dx * $dx + $dy * $dy ) );
	$pts[0]   = array( $first[0] + $dx / $len * $trim, $first[1] + $dy / $len * $trim );
	$last     = $pts[ $n - 1 ];
	$dx       = $last[0] - $pts[ $n - 2 ][0];
	$dy       = $last[1] - $pts[ $n - 2 ][1];
	$len      = max( 1e-6, sqrt( $dx * $dx + $dy * $dy ) );
	$pts[ $n - 1 ] = array( $last[0] - $dx / $len * $trim, $last[1] - $dy / $len * $trim );

	$d = 'M' . vnises_gt_n( $pts[0][0] ) . ' ' . vnises_gt_n( $pts[0][1] );
	for ( $i = 0; $i < $n - 1; $i++ ) {
		$p0 = $pts[ max( 0, $i - 1 ) ];
		$p1 = $pts[ $i ];
		$p2 = $pts[ $i + 1 ];
		$p3 = $pts[ min( $n - 1, $i + 2 ) ];
		$c1 = array( $p1[0] + ( $p2[0] - $p0[0] ) / 6, $p1[1] + ( $p2[1] - $p0[1] ) / 6 );
		$c2 = array( $p2[0] - ( $p3[0] - $p1[0] ) / 6, $p2[1] - ( $p3[1] - $p1[1] ) / 6 );
		$d .= 'C' . vnises_gt_n( $c1[0] ) . ' ' . vnises_gt_n( $c1[1] ) . ' ' . vnises_gt_n( $c2[0] ) . ' ' . vnises_gt_n( $c2[1] ) . ' ' . vnises_gt_n( $p2[0] ) . ' ' . vnises_gt_n( $p2[1] );
	}
	return $d;
}

/**
 * Hình 06a — mặt cắt Trái Đất / khí quyển / quỹ đạo ISS theo tỉ lệ bán kính.
 * R⊕ = 900 đơn vị ⇒ 100 km ≈ 14,1 đơn vị; 400 km ≈ 56,5 đơn vị.
 */
function vnises_gt_svg_iss( $ctx, $aria ) {
	$cx    = 170;
	$cy    = 1040;
	$re    = 900;
	$ra    = $re + 900 * 100 / 6371;
	$ri    = $re + 900 * 400 / 6371;
	$arc   = function ( $r ) use ( $cx, $cy ) {
		$dx = 170;
		$y  = $cy - sqrt( $r * $r - $dx * $dx );
		return array( 'M0 ' . vnises_gt_n( $y ) . 'A' . vnises_gt_n( $r ) . ' ' . vnises_gt_n( $r ) . ' 0 0 1 340 ' . vnises_gt_n( $y ), $y );
	};
	$earth = $arc( $re );
	$atm   = $arc( $ra );
	$iss   = $arc( $ri );
	$s     = '<svg class="vngt-svg" viewBox="0 0 340 230" role="img" aria-label="' . esc_attr( $aria ) . '" focusable="false">';
	$s    .= '<path class="vngt-g-atm" d="' . $atm[0] . 'L340 230H0Z"/>';
	$s    .= '<path class="vngt-g-earth" d="' . $earth[0] . 'L340 230H0Z"/>';
	$s    .= '<path class="vngt-g vngt-g--main" data-vngt-draw d="' . $earth[0] . '"/>';
	$s    .= '<path class="vngt-g vngt-g--accent vngt-g--dash" d="' . $iss[0] . '"/>';
	$top_i = $cy - $ri;
	$s    .= '<rect class="vngt-g-iss" x="' . ( $cx - 7 ) . '" y="' . vnises_gt_n( $top_i - 2 ) . '" width="14" height="4"/><rect class="vngt-g-iss" x="' . ( $cx - 1.5 ) . '" y="' . vnises_gt_n( $top_i - 6 ) . '" width="3" height="12"/>';
	$s    .= '<text class="vngt-g-label vngt-g-label--accent" x="' . ( $cx + 14 ) . '" y="' . vnises_gt_n( $top_i - 10 ) . '">ISS · ~400 km</text>';
	$s    .= '<text class="vngt-g-label" x="332" y="' . vnises_gt_n( $atm[1] - 26 ) . '" text-anchor="end">khí quyển · ~100 km</text>';
	$s    .= '<path class="vngt-g vngt-g--leader" d="M300 ' . vnises_gt_n( $atm[1] - 22 ) . 'V' . vnises_gt_n( $atm[1] + 4 ) . '"/>';
	$s    .= '<text class="vngt-g-label" x="' . $cx . '" y="' . vnises_gt_n( $cy - $re + 44 ) . '" text-anchor="middle">bề mặt Trái Đất</text>';
	$s    .= '<text class="vngt-g-label vngt-g-label--small" x="' . $cx . '" y="' . vnises_gt_n( $cy - $re + 66 ) . '" text-anchor="middle">bán kính ~6.371 km</text>';
	$s    .= '</svg>';
	return $s;
}

/**
 * Hình 06b — vết quỹ đạo trên mặt đất: quỹ đạo tròn, i = 51,6°, chu kỳ ≈ 92,7 phút, hai vòng,
 * Trái Đất quay 360° mỗi 1436,07 phút (ngày thiên văn). Bản đồ phép chiếu trụ đều (equirectangular).
 */
function vnises_gt_svg_groundtrack( $ctx, $aria ) {
	$inc    = deg2rad( 51.6 );
	$period = 92.7;
	$we     = 360 / 1436.07;
	$lon0   = -150.0;
	$map    = function ( $lon, $lat ) {
		return array( 10 + ( $lon + 180 ) / 360 * 320, 15 + ( 90 - $lat ) / 180 * 160 );
	};
	$segments = array();
	$current  = array();
	$prev_dl  = null;
	$prev_lon = null;
	$steps    = 480;
	for ( $k = 0; $k <= $steps; $k++ ) {
		$t   = 2 * $period * $k / $steps;
		$u   = 2 * M_PI * $t / $period;
		$lat = rad2deg( asin( sin( $inc ) * sin( $u ) ) );
		$dl  = rad2deg( atan2( cos( $inc ) * sin( $u ), cos( $u ) ) );
		if ( null !== $prev_dl ) {
			while ( $dl - $prev_dl > 180 ) {
				$dl -= 360;
			}
			while ( $dl - $prev_dl < -180 ) {
				$dl += 360;
			}
		}
		$prev_dl = $dl;
		$lon     = $lon0 + $dl - $we * $t;
		$lon     = fmod( $lon + 540, 360 ) - 180;
		if ( null !== $prev_lon && abs( $lon - $prev_lon ) > 180 ) {
			$segments[] = $current;
			$current    = array();
		}
		$current[] = $map( $lon, $lat );
		$prev_lon  = $lon;
	}
	$segments[] = $current;

	$s = '<svg class="vngt-svg" viewBox="0 0 340 230" role="img" aria-label="' . esc_attr( $aria ) . '" focusable="false">';
	$grid = '';
	for ( $lon = -180; $lon <= 180; $lon += 30 ) {
		$p     = $map( $lon, 0 );
		$grid .= 'M' . vnises_gt_n( $p[0] ) . ' 15V175';
	}
	for ( $lat = -90; $lat <= 90; $lat += 30 ) {
		$p     = $map( 0, $lat );
		$grid .= 'M10 ' . vnises_gt_n( $p[1] ) . 'H330';
	}
	$s .= '<path class="vngt-g vngt-g--grid" d="' . $grid . '"/>';
	$eq = $map( 0, 0 );
	$s .= '<path class="vngt-g vngt-g--faint" d="M10 ' . vnises_gt_n( $eq[1] ) . 'H330"/>';
	$pn = $map( 0, 51.6 );
	$ps = $map( 0, -51.6 );
	$s .= '<path class="vngt-g vngt-g--faint vngt-g--dash" d="M10 ' . vnises_gt_n( $pn[1] ) . 'H330M10 ' . vnises_gt_n( $ps[1] ) . 'H330"/>';
	foreach ( $segments as $seg ) {
		if ( count( $seg ) > 1 ) {
			$s .= '<path class="vngt-g vngt-g--track" d="' . vnises_gt_polyline( $seg ) . '"/>';
		}
	}
	$s .= '<text class="vngt-g-label vngt-g-label--small" x="14" y="' . vnises_gt_n( $eq[1] - 4 ) . '">xích đạo</text>';
	$s .= '<text class="vngt-g-label vngt-g-label--small" x="326" y="' . vnises_gt_n( $pn[1] - 4 ) . '" text-anchor="end">51,6°B</text>';
	$s .= '<text class="vngt-g-label vngt-g-label--small" x="326" y="' . vnises_gt_n( $ps[1] + 13 ) . '" text-anchor="end">51,6°N</text>';
	$s .= '<text class="vngt-g-label vngt-g-label--small" x="10" y="196">kinh độ −180° … +180°</text>';
	$s .= '</svg>';
	return $s;
}

/**
 * Hình 06c — đĩa Mặt Trời với lưới vĩ độ – kinh độ (trục quay giả định nằm trong mặt phẳng bầu trời).
 */
function vnises_gt_svg_sun_grid( $ctx, $aria ) {
	$gid = $ctx['id'] . '-sun2';
	$cx  = 170;
	$cy  = 112;
	$r   = 86;
	$s   = '<svg class="vngt-svg" viewBox="0 0 340 230" role="img" aria-label="' . esc_attr( $aria ) . '" focusable="false">';
	$s  .= '<defs><radialGradient id="' . esc_attr( $gid ) . '" cx="50%" cy="50%" r="50%"><stop offset="0" stop-color="#f4e6c4"/><stop offset=".55" stop-color="#e8c784"/><stop offset=".85" stop-color="#d4a457"/><stop offset="1" stop-color="#a8742f"/></radialGradient></defs>';
	$s  .= '<path class="vngt-g vngt-g--faint vngt-g--dash" d="M' . $cx . ' ' . ( $cy - $r - 18 ) . 'V' . ( $cy + $r + 18 ) . '"/>';
	$s  .= '<circle cx="' . $cx . '" cy="' . $cy . '" r="' . $r . '" fill="url(#' . esc_attr( $gid ) . ')"/>';
	$grid = '';
	foreach ( array( -60, -30, 30, 60 ) as $lat ) {
		$y     = $cy - $r * sin( deg2rad( $lat ) );
		$hw    = $r * cos( deg2rad( $lat ) );
		$grid .= 'M' . vnises_gt_n( $cx - $hw ) . ' ' . vnises_gt_n( $y ) . 'H' . vnises_gt_n( $cx + $hw );
	}
	foreach ( array( -60, -30, 30, 60 ) as $lon ) {
		$rx    = abs( $r * sin( deg2rad( $lon ) ) );
		$grid .= 'M' . $cx . ' ' . ( $cy - $r ) . 'A' . vnises_gt_n( $rx ) . ' ' . $r . ' 0 0 ' . ( $lon > 0 ? '1' : '0' ) . ' ' . $cx . ' ' . ( $cy + $r );
	}
	$grid .= 'M' . $cx . ' ' . ( $cy - $r ) . 'V' . ( $cy + $r );
	$s    .= '<path class="vngt-g-sungrid" d="' . $grid . '"/>';
	$s    .= '<path class="vngt-g-sungrid vngt-g-sungrid--eq" d="M' . ( $cx - $r ) . ' ' . $cy . 'H' . ( $cx + $r ) . '"/>';
	$s    .= '<text class="vngt-g-label vngt-g-label--small" x="' . ( $cx + 6 ) . '" y="' . ( $cy - $r - 8 ) . '">trục quay</text>';
	$s    .= '<path class="vngt-g vngt-g--leader" d="M' . ( $cx + $r ) . ' ' . $cy . 'H' . ( $cx + $r + 14 ) . '"/>';
	$s    .= '<text class="vngt-g-label vngt-g-label--small" x="' . ( $cx + $r + 18 ) . '" y="' . ( $cy + 4 ) . '">xích đạo</text>';
	$s    .= '</svg>';
	return $s;
}

/**
 * Hình 07 — sơ đồ khái niệm năm loại thông tin. Các điểm là cố định (không ngẫu nhiên) và không phải số liệu.
 */
function vnises_gt_svg_integrity( $ctx, $c ) {
	$model = function ( $x ) {
		return 250 - 175 * ( 1 - exp( -( $x - 60 ) / 150 ) );
	};
	$curve = array();
	for ( $x = 60; $x <= 380; $x += 8 ) {
		$curve[] = array( $x, $model( $x ) );
	}
	$ext = array();
	$hi  = array();
	$lo  = array();
	for ( $x = 380; $x <= 516; $x += 8 ) {
		$w     = ( $x - 380 ) * 0.32;
		$ext[] = array( $x, $model( $x ) );
		$hi[]  = array( $x, $model( $x ) - $w );
		$lo[]  = array( $x, $model( $x ) + $w );
	}
	$cone = vnises_gt_polyline( $hi );
	$back = array_reverse( $lo );
	foreach ( $back as $p ) {
		$cone .= 'L' . vnises_gt_n( $p[0] ) . ' ' . vnises_gt_n( $p[1] );
	}
	$cone .= 'Z';
	$hid   = $ctx['id'] . '-hatch';

	$s  = '<svg class="vngt-svg vngt-svg--chart" viewBox="0 0 560 300" role="img" aria-label="' . esc_attr( $c['chart']['aria'] ) . '" focusable="false">';
	$s .= '<defs><pattern id="' . esc_attr( $hid ) . '" width="8" height="8" patternUnits="userSpaceOnUse" patternTransform="rotate(45)"><path class="vngt-g-hatch" d="M0 0V8"/></pattern></defs>';
	$s .= '<rect x="380" y="24" width="156" height="226" fill="url(#' . esc_attr( $hid ) . ')" class="vngt-g-hatchbox"/>';
	$s .= '<path class="vngt-g vngt-g--faint" d="M60 250H540M60 250V20"/>';
	$s .= '<text class="vngt-g-label vngt-g-label--chart" x="540" y="274" text-anchor="end">' . esc_html( $c['chart']['x'] ) . '</text>';
	$s .= '<text class="vngt-g-label vngt-g-label--chart" x="66" y="18">' . esc_html( $c['chart']['y'] ) . '</text>';
	$s .= '<path class="vngt-g vngt-g--asm" d="M380 24V250"/>';
	$s .= '<path class="vngt-g-cone" d="' . $cone . '"/>';
	$s .= '<path class="vngt-g vngt-g--model vngt-g--dash" d="' . vnises_gt_polyline( $curve ) . '"/>';
	$s .= '<path class="vngt-g vngt-g--inf" d="' . vnises_gt_polyline( $ext ) . '" stroke-dasharray="1.5 5"/>';
	$s .= '<circle class="vngt-g-infend" cx="516" cy="' . vnises_gt_n( $model( 516 ) ) . '" r="5"/>';
	// Quan sát: điểm đặc + thanh sai số. Dữ liệu từ nguồn: ô vuông rỗng + thanh sai số.
	$obs = array( array( 92, -9 ), array( 152, 7 ), array( 212, -6 ), array( 272, 8 ), array( 332, -5 ) );
	$dat = array( array( 122, 6 ), array( 182, -8 ), array( 242, 5 ), array( 302, -7 ) );
	foreach ( $dat as $p ) {
		$y  = $model( $p[0] ) + $p[1];
		$s .= '<path class="vngt-g vngt-g--err" d="M' . $p[0] . ' ' . vnises_gt_n( $y - 15 ) . 'V' . vnises_gt_n( $y + 15 ) . 'M' . ( $p[0] - 4 ) . ' ' . vnises_gt_n( $y - 15 ) . 'h8M' . ( $p[0] - 4 ) . ' ' . vnises_gt_n( $y + 15 ) . 'h8"/>';
		$s .= '<rect class="vngt-g-data" x="' . ( $p[0] - 4.5 ) . '" y="' . vnises_gt_n( $y - 4.5 ) . '" width="9" height="9"/>';
	}
	foreach ( $obs as $p ) {
		$y  = $model( $p[0] ) + $p[1];
		$s .= '<path class="vngt-g vngt-g--err" d="M' . $p[0] . ' ' . vnises_gt_n( $y - 11 ) . 'V' . vnises_gt_n( $y + 11 ) . 'M' . ( $p[0] - 4 ) . ' ' . vnises_gt_n( $y - 11 ) . 'h8M' . ( $p[0] - 4 ) . ' ' . vnises_gt_n( $y + 11 ) . 'h8"/>';
		$s .= '<circle class="vngt-g-obs" cx="' . $p[0] . '" cy="' . vnises_gt_n( $y ) . '" r="4.5"/>';
	}
	// Nhãn đánh số (khớp với danh sách phân loại bên cạnh).
	$tags = array(
		array( 1, 152, $model( 152 ) + 7 + 34, 'OBSERVATION' ),
		array( 2, 242, $model( 242 ) + 5 + 38, 'DATA' ),
		array( 3, 100, $model( 100 ) - 34, 'SIMULATION' ),
		array( 4, 380, 42, 'ASSUMPTION' ),
		array( 5, 500, $model( 500 ) - 64, 'INFERENCE' ),
	);
	foreach ( $tags as $t ) {
		$anchor = ( 4 === $t[0] || 5 === $t[0] ) ? 'end' : 'start';
		$tx     = ( 'end' === $anchor ) ? $t[1] - 22 : $t[1] + 16;
		if ( 5 === $t[0] ) {
			$tx = $t[1] + 26;
		}
		$s .= '<g class="vngt-chart-tag"><circle class="vngt-g-badge" cx="' . $t[1] . '" cy="' . vnises_gt_n( $t[2] ) . '" r="10"/><text class="vngt-g-badge-num" x="' . $t[1] . '" y="' . vnises_gt_n( $t[2] + 4 ) . '" text-anchor="middle">' . (int) $t[0] . '</text>';
		$s .= '<text class="vngt-g-label vngt-g-label--chart vngt-chart-tag__text" lang="en" x="' . vnises_gt_n( $tx ) . '" y="' . vnises_gt_n( $t[2] + 4 ) . '" text-anchor="' . $anchor . '">' . esc_html( $t[3] ) . '</text></g>';
	}
	$s .= '</svg>';
	return $s;
}

/* =============================================================================
 * 5. SCOPED CSS
 * Mọi selector nằm trong .vngt-root. Không chạm html/body/p/h1… ở phạm vi toàn cục.
 * Màu sắc chỉ khai báo một lần dưới dạng custom properties.
 * ========================================================================== */

/**
 * @return string
 */
function vnises_gt_css() {
	return <<<'CSS'
/* ---- 5.1 Tokens ---------------------------------------------------------- */
.vngt-root{
	--vngt-bg:#0b0c0d;
	--vngt-surface:#111315;
	--vngt-surface-2:#16191c;
	--vngt-text:#eeede8;
	--vngt-text-2:#cfcec7;
	--vngt-muted:#a2a29b;
	--vngt-line:rgba(238,237,232,.12);
	--vngt-line-2:rgba(238,237,232,.24);
	--vngt-line-3:rgba(238,237,232,.46);
	--vngt-accent:#d4a957;
	--vngt-accent-2:rgba(212,169,87,.6);
	--vngt-accent-soft:rgba(212,169,87,.15);
	--vngt-focus:#f2cc80;
	--vngt-font:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,"Noto Sans",sans-serif;
	--vngt-mono:ui-monospace,SFMono-Regular,"SF Mono",Menlo,Consolas,"Liberation Mono","DejaVu Sans Mono",monospace;
	--vngt-max:1240px;
	--vngt-gutter:clamp(16px,5vw,48px);
	--vngt-ease:cubic-bezier(.2,.7,.2,1);
	position:relative;
	display:block;
	width:100%;
	max-width:none;
	margin:0;
	padding:0;
	overflow:hidden;
	overflow:clip;
	background:var(--vngt-bg);
	color:var(--vngt-text);
	font-family:var(--vngt-font);
	font-size:max(1rem,17px);
	font-weight:400;
	line-height:1.62;
	letter-spacing:normal;
	text-align:left;
	text-transform:none;
	overflow-wrap:break-word;
	-webkit-text-size-adjust:100%;
	text-size-adjust:100%;
	-webkit-font-smoothing:antialiased;
	-moz-osx-font-smoothing:grayscale;
	isolation:isolate;
	color-scheme:dark;
	container:vngt / inline-size;
}
@media (min-width:1024px){.vngt-root{font-size:max(1rem,18px)}}
@media (min-width:1280px){.vngt-root{font-size:max(1.0625rem,19px)}}

/* ---- 5.2 Scoped reset (chống CSS của theme) ------------------------------ */
.vngt-root *,.vngt-root *::before,.vngt-root *::after{box-sizing:border-box}
.vngt-root h1,.vngt-root h2,.vngt-root h3,.vngt-root h4,.vngt-root h5,.vngt-root h6{margin:0;padding:0;border:0;background:none;color:inherit;font-family:inherit;font-size:inherit;font-style:normal;font-weight:600;line-height:1.22;letter-spacing:normal;text-transform:none;text-align:inherit;text-shadow:none;clear:none}
.vngt-root h1::before,.vngt-root h2::before,.vngt-root h3::before,.vngt-root h4::before,.vngt-root h5::before,.vngt-root h1::after,.vngt-root h2::after,.vngt-root h3::after,.vngt-root h4::after,.vngt-root h5::after{content:none}
.vngt-root p,.vngt-root ol,.vngt-root ul,.vngt-root li,.vngt-root dl,.vngt-root dt,.vngt-root dd,.vngt-root figure,.vngt-root figcaption,.vngt-root nav,.vngt-root header,.vngt-root section,.vngt-root output,.vngt-root label{margin:0;padding:0;border:0;background:none;color:inherit;font-family:inherit;font-size:inherit;font-style:normal;line-height:inherit;letter-spacing:normal;text-transform:none;text-shadow:none;max-width:none}
.vngt-root ol,.vngt-root ul{list-style:none}
.vngt-root ol li::before,.vngt-root ul li::before,.vngt-root ol li::after,.vngt-root ul li::after{content:none}
.vngt-root li::marker{content:none}
.vngt-root em,.vngt-root i{font-style:italic}
.vngt-root strong{font-weight:600;color:var(--vngt-text)}
.vngt-root sup,.vngt-root sub{font-size:.72em;line-height:0;position:relative;vertical-align:baseline}
.vngt-root sup{top:-.5em}
.vngt-root sub{bottom:-.25em}
.vngt-root a{color:inherit;text-decoration:none;background:none;border:0;box-shadow:none;outline-offset:3px}
.vngt-root button{-webkit-appearance:none;appearance:none;margin:0;padding:0;border:0;border-radius:0;background:none;box-shadow:none;color:inherit;font:inherit;line-height:inherit;letter-spacing:inherit;text-transform:none;text-align:inherit;text-shadow:none;width:auto;height:auto;min-height:0;cursor:pointer;-webkit-tap-highlight-color:transparent}
.vngt-root svg{display:block;max-width:100%;height:auto;overflow:hidden}
.vngt-root [hidden]{display:none !important}
.vngt-root :focus{outline:2px solid var(--vngt-focus);outline-offset:3px}
.vngt-root :focus:not(:focus-visible){outline:none}
.vngt-root :focus-visible{outline:2px solid var(--vngt-focus);outline-offset:3px}
.vngt-root .vngt-sr{position:absolute !important;width:1px !important;height:1px !important;padding:0 !important;margin:-1px !important;overflow:hidden !important;clip:rect(0,0,0,0) !important;white-space:nowrap !important;border:0 !important}

/* ---- 5.3 Layout primitives ---------------------------------------------- */
.vngt-root .vngt-shell{width:100%;max-width:var(--vngt-max);margin:0 auto;padding:0 var(--vngt-gutter)}
.vngt-root .vngt-scene{position:relative;padding:clamp(72px,10vw,150px) 0;padding:clamp(72px,10cqi,150px) 0;border-top:1px solid var(--vngt-line);scroll-margin-top:var(--vngt-scroll-offset,24px)}
.vngt-root .vngt-scene--opening{border-top:0;padding-top:clamp(28px,5vw,64px);padding-top:clamp(28px,5cqi,64px)}
.vngt-root .vngt-scene--opening::before{content:"";position:absolute;left:0;right:0;top:0;height:min(900px,90%);background-image:linear-gradient(var(--vngt-line) 1px,transparent 1px),linear-gradient(90deg,var(--vngt-line) 1px,transparent 1px);background-size:72px 72px;background-position:center top;opacity:.45;-webkit-mask-image:linear-gradient(180deg,#000,rgba(0,0,0,0));mask-image:linear-gradient(180deg,#000,rgba(0,0,0,0));pointer-events:none;z-index:-1}
.vngt-root .vngt-scene--integrity,.vngt-root .vngt-scene--standards{background:var(--vngt-surface)}

.vngt-root .vngt-eyebrow{display:flex;align-items:center;flex-wrap:wrap;gap:.7em;margin:0 0 1.4em;font-size:.76em;font-weight:500;letter-spacing:.14em;text-transform:uppercase;color:var(--vngt-muted);line-height:1.4}
.vngt-root .vngt-eyebrow__idx{font-family:var(--vngt-mono);color:var(--vngt-accent);letter-spacing:.04em}
.vngt-root .vngt-eyebrow__total{color:var(--vngt-muted)}
.vngt-root .vngt-eyebrow__rule{display:block;width:2.4em;height:1px;background:var(--vngt-line-2)}

.vngt-root .vngt-title{font-size:clamp(1.7em,1.05em + 2.4vw,3.05em);font-size:clamp(1.7em,1.05em + 2.4cqi,3.05em);font-weight:500;line-height:1.18;letter-spacing:-.012em;color:var(--vngt-text);max-width:19em;text-wrap:balance}
.vngt-root .vngt-title--caps{text-transform:uppercase;letter-spacing:.01em;font-size:clamp(1.55em,1em + 2vw,2.6em);font-size:clamp(1.55em,1em + 2cqi,2.6em);line-height:1.2}
.vngt-root .vngt-title--wide{max-width:24em}
.vngt-root .vngt-title__second{display:block;color:var(--vngt-muted)}
.vngt-root .vngt-em{font-style:normal;color:var(--vngt-text);background-image:linear-gradient(var(--vngt-accent),var(--vngt-accent));background-repeat:no-repeat;background-size:100% 1px;background-position:0 96%;padding-bottom:.04em}
.vngt-root .vngt-lead{font-size:1.14em;line-height:1.58;color:var(--vngt-text)}
.vngt-root .vngt-prose p{color:var(--vngt-text-2);max-width:36em}
.vngt-root .vngt-prose p.vngt-lead{color:var(--vngt-text)}
.vngt-root .vngt-prose p+p{margin-top:1em}
.vngt-root .vngt-caption{font-size:.84em;line-height:1.55;color:var(--vngt-muted)}
.vngt-root .vngt-caption i{color:var(--vngt-text-2)}
.vngt-root .vngt-subhead{font-size:.8em;font-weight:600;letter-spacing:.12em;text-transform:uppercase;color:var(--vngt-muted);margin:0 0 1.1em}

.vngt-root .vngt-intro{display:grid;gap:28px;margin-bottom:clamp(40px,6vw,80px);margin-bottom:clamp(40px,6cqi,80px)}
@container vngt (min-width:1024px){
	.vngt-root .vngt-intro{grid-template-columns:minmax(0,6fr) minmax(0,5fr);column-gap:clamp(40px,6vw,96px);column-gap:clamp(40px,6cqi,96px);align-items:end}
	.vngt-root .vngt-intro__body{padding-bottom:.3em}
}

/* Nhãn loại thông tin của hình */
.vngt-root .vngt-ftag{display:flex;flex-wrap:wrap;align-items:center;gap:.35em .6em;margin:0 0 .9em;font-size:max(12px,.7em);line-height:1.3;letter-spacing:.1em;text-transform:uppercase;color:var(--vngt-muted)}
.vngt-root .vngt-ftag__type{display:inline-block;padding:.28em .6em;border:1px solid var(--vngt-line-2);border-radius:2px;color:var(--vngt-text);font-weight:600;letter-spacing:.12em}
.vngt-root .vngt-ftag__note{letter-spacing:.06em}

/* ---- 5.4 SVG graphic language ------------------------------------------- */
.vngt-root .vngt-svg{width:100%;height:auto}
.vngt-root .vngt-g{fill:none;stroke:var(--vngt-line-3);stroke-width:1.2;stroke-linecap:round;stroke-linejoin:round}
.vngt-root .vngt-g--faint{stroke:var(--vngt-line-2);stroke-width:1}
.vngt-root .vngt-g--grid{stroke:var(--vngt-line);stroke-width:.8}
.vngt-root .vngt-g--main{stroke:var(--vngt-text);stroke-width:1.6}
.vngt-root .vngt-g--accent{stroke:var(--vngt-accent);stroke-width:1.5}
.vngt-root .vngt-g--dash{stroke-dasharray:4 5}
.vngt-root .vngt-g--env{stroke:var(--vngt-accent-2);stroke-width:1.2}
.vngt-root .vngt-g--axis{fill:var(--vngt-line-3);stroke:none}
.vngt-root .vngt-g--radius{stroke:var(--vngt-accent);stroke-width:1.2}
.vngt-root .vngt-g--leader{stroke:var(--vngt-line-3);stroke-width:.9}
.vngt-root .vngt-g--ref{stroke:var(--vngt-line-3);stroke-width:1.4;stroke-dasharray:5 6}
.vngt-root .vngt-g--vel{stroke:var(--vngt-text);stroke-width:1.6}
.vngt-root .vngt-g--track{stroke:var(--vngt-accent);stroke-width:1.5}
.vngt-root .vngt-g--model{stroke:var(--vngt-text);stroke-width:1.8}
.vngt-root .vngt-g--inf{stroke:var(--vngt-accent);stroke-width:2}
.vngt-root .vngt-g--asm{stroke:var(--vngt-text-2);stroke-width:1.2;stroke-dasharray:2 4}
.vngt-root .vngt-g--err{stroke:var(--vngt-text-2);stroke-width:1}
.vngt-root .vngt-g-wedge{fill:var(--vngt-accent-soft);stroke:var(--vngt-accent);stroke-width:1;stroke-linejoin:round}
.vngt-root .vngt-g-focus{fill:var(--vngt-accent);stroke:none}
.vngt-root .vngt-g-focus2{fill:var(--vngt-bg);stroke:var(--vngt-text-2);stroke-width:1.2}
.vngt-root .vngt-g-body{fill:var(--vngt-text);stroke:var(--vngt-bg);stroke-width:2}
.vngt-root .vngt-g-tick{fill:var(--vngt-text-2);stroke:none}
.vngt-root .vngt-g-knob{fill:var(--vngt-bg);stroke:var(--vngt-accent);stroke-width:2}
.vngt-root .vngt-g-dot{fill:var(--vngt-bg);stroke:none}
.vngt-root .vngt-g-dot2{fill:var(--vngt-text);stroke:none}
.vngt-root .vngt-g-arrowhead{fill:var(--vngt-text);stroke:none}
.vngt-root .vngt-g-arrowhead--accent{fill:var(--vngt-accent)}
.vngt-root .vngt-g-cut{stroke:rgba(11,12,13,.55);stroke-width:1}
.vngt-root .vngt-g-cut--conv{fill:#6f5124}
.vngt-root .vngt-g-cut--rad{fill:#4a3618}
.vngt-root .vngt-g-cut--core{fill:#f4e6c4}
.vngt-root .vngt-g-atm{fill:rgba(170,190,210,.16)}
.vngt-root .vngt-g-earth{fill:var(--vngt-surface-2)}
.vngt-root .vngt-g-iss{fill:var(--vngt-accent)}
.vngt-root .vngt-g-sungrid{fill:none;stroke:rgba(11,12,13,.42);stroke-width:.9}
.vngt-root .vngt-g-sungrid--eq{stroke:rgba(11,12,13,.7);stroke-width:1.2}
.vngt-root .vngt-g-hatch{stroke:var(--vngt-line);stroke-width:1.2}
.vngt-root .vngt-g-cone{fill:var(--vngt-accent-soft);stroke:none}
.vngt-root .vngt-g-infend{fill:var(--vngt-bg);stroke:var(--vngt-accent);stroke-width:1.6}
.vngt-root .vngt-g-obs{fill:var(--vngt-text);stroke:none}
.vngt-root .vngt-g-data{fill:var(--vngt-surface);stroke:var(--vngt-text);stroke-width:1.4}
.vngt-root .vngt-g-badge{fill:var(--vngt-surface);stroke:var(--vngt-line-3);stroke-width:1}
.vngt-root .vngt-g-badge-num{fill:var(--vngt-text);font-family:var(--vngt-mono);font-size:11px}
.vngt-root .vngt-g-label{fill:var(--vngt-muted);font-family:var(--vngt-font);font-size:12.5px;letter-spacing:.02em}
.vngt-root .vngt-g-label--accent{fill:var(--vngt-accent)}
.vngt-root .vngt-g-label--small{font-size:10.5px}
.vngt-root .vngt-g-label--lab{font-size:19px}
.vngt-root .vngt-g-label--chart{font-size:12.5px;letter-spacing:.06em}
@container vngt (min-width:768px){.vngt-root .vngt-g-label--lab{font-size:13.5px}}
@container vngt (max-width:767.98px){.vngt-root .vngt-chart-tag__text{display:none}.vngt-root .vngt-g-label--chart{font-size:16px}.vngt-root .vngt-g-badge-num{font-size:13px}}

/* ---- 5.5 Scene 01 — opening --------------------------------------------- */
.vngt-root .vngt-mast{display:flex;flex-wrap:wrap;align-items:baseline;justify-content:space-between;gap:.4em 2em;padding-bottom:1.1em;margin-bottom:clamp(48px,8vw,112px);margin-bottom:clamp(48px,8cqi,112px);border-bottom:1px solid var(--vngt-line)}
.vngt-root .vngt-mast__title{font-size:.8em;font-weight:600;letter-spacing:.16em;text-transform:uppercase;color:var(--vngt-text)}
.vngt-root .vngt-mast__meta{font-size:.78em;color:var(--vngt-muted);letter-spacing:.02em}
.vngt-root .vngt-title--opening{font-size:clamp(1.85em,1.05em + 3vw,3.55em);font-size:clamp(1.85em,1.05em + 3cqi,3.55em);max-width:17.5em;line-height:1.16}
.vngt-root .vngt-opening__head{margin-bottom:clamp(48px,7vw,96px);margin-bottom:clamp(48px,7cqi,96px)}

.vngt-root .vngt-trio{display:grid;gap:48px}
.vngt-root .vngt-trio__item{display:grid;gap:18px;align-content:start;padding-top:18px;border-top:1px solid var(--vngt-line-2);min-width:0}
.vngt-root .vngt-trio__label{font-size:.78em;font-weight:600;letter-spacing:.14em;text-transform:uppercase;color:var(--vngt-accent)}
.vngt-root .vngt-trio__label--plain{color:var(--vngt-text);font-size:1.1em;letter-spacing:0;text-transform:none;font-weight:500}
.vngt-root .vngt-trio__claim{font-size:1.28em;line-height:1.32;color:var(--vngt-text);margin-top:.3em;max-width:16em}
.vngt-root .vngt-trio__micro{font-size:.72em;font-weight:600;letter-spacing:.14em;text-transform:uppercase;color:var(--vngt-accent);margin-bottom:.5em}
.vngt-root .vngt-trio__text{color:var(--vngt-text-2);margin-top:.45em;max-width:26em}
.vngt-root .vngt-trio__fig{min-width:0}
.vngt-root .vngt-trio__fig .vngt-svg{margin:4px 0 14px;max-width:440px}
.vngt-root .vngt-equation{display:block;margin:0 0 .5em;font-family:"Cambria Math","STIX Two Math","Times New Roman",serif;font-size:1.22em;color:var(--vngt-text);letter-spacing:.01em}
.vngt-root .vngt-equation i{color:var(--vngt-text)}
@container vngt (min-width:768px) and (max-width:1023.98px){
	.vngt-root .vngt-trio__item{grid-template-columns:minmax(0,1fr) minmax(0,1fr);column-gap:36px}
	.vngt-root .vngt-trio__head{grid-column:2;grid-row:1}
	.vngt-root .vngt-trio__fig{grid-column:1;grid-row:1}
}
@container vngt (min-width:1024px){
	.vngt-root .vngt-trio{grid-template-columns:repeat(3,minmax(0,1fr));gap:clamp(28px,3.4vw,56px);gap:clamp(28px,3.4cqi,56px)}
	.vngt-root .vngt-trio__head{min-height:6.4em}
	.vngt-root .vngt-trio--station .vngt-trio__head{min-height:9.2em}
}

.vngt-root .vngt-opening__close{margin-top:clamp(64px,9vw,128px);margin-top:clamp(64px,9cqi,128px);display:grid;gap:22px}
.vngt-root .vngt-opening__statement{font-size:clamp(1.3em,1em + 1.1vw,1.85em);font-size:clamp(1.3em,1em + 1.1cqi,1.85em);line-height:1.38;color:var(--vngt-text);max-width:26em;text-wrap:balance}
.vngt-root .vngt-triad{display:flex;flex-wrap:wrap;align-items:center;gap:.5em .9em;font-size:clamp(.9em,.8em + .45vw,1.1em);font-size:clamp(.9em,.8em + .45cqi,1.1em);font-weight:600;letter-spacing:.22em;text-transform:uppercase;color:var(--vngt-text)}
.vngt-root .vngt-triad__dot{color:var(--vngt-accent);letter-spacing:0}

.vngt-root .vngt-toc{margin-top:clamp(56px,8vw,104px);margin-top:clamp(56px,8cqi,104px);padding-top:18px;border-top:1px solid var(--vngt-line)}
.vngt-root .vngt-toc__title{font-size:.72em;letter-spacing:.14em;text-transform:uppercase;color:var(--vngt-muted);margin-bottom:10px}
.vngt-root .vngt-toc__list{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:0 12px}
.vngt-root .vngt-toc__link{display:flex;align-items:center;gap:.6em;min-height:44px;padding:6px 0;font-size:.86em;color:var(--vngt-text-2);border-bottom:1px solid transparent;transition:color .2s ease,border-color .2s ease}
.vngt-root .vngt-toc__link:hover{color:var(--vngt-text);border-bottom-color:var(--vngt-line-2)}
.vngt-root .vngt-toc__num{font-family:var(--vngt-mono);font-size:.88em;color:var(--vngt-accent)}
@container vngt (max-width:429.98px){.vngt-root .vngt-toc__list{grid-template-columns:repeat(2,minmax(0,1fr))}}
@container vngt (min-width:768px){.vngt-root .vngt-toc__list{grid-template-columns:repeat(5,minmax(0,1fr))}}
@container vngt (min-width:1280px){.vngt-root .vngt-toc__list{grid-template-columns:repeat(9,minmax(0,1fr))}}

/* ---- 5.6 Scene 02 — identity -------------------------------------------- */
.vngt-root .vngt-identity{display:grid;gap:20px;margin-bottom:clamp(56px,8vw,112px);margin-bottom:clamp(56px,8cqi,112px)}
.vngt-root .vngt-wordmark{font-size:clamp(3.2em,17vw,9em);font-size:clamp(3.2em,17cqi,9em);font-weight:600;line-height:.92;letter-spacing:.015em;white-space:nowrap;color:var(--vngt-text)}
.vngt-root .vngt-expansion{font-size:clamp(1em,.85em + .9vw,1.6em);font-size:clamp(1em,.85em + .9cqi,1.6em);line-height:1.35;color:var(--vngt-muted);letter-spacing:.005em;max-width:24em}
.vngt-root .vngt-expansion__initial{color:var(--vngt-accent);font-weight:600}
.vngt-root .vngt-expansion__joiner{color:var(--vngt-muted)}
.vngt-root .vngt-identity__lead{margin-top:clamp(12px,2vw,28px);margin-top:clamp(12px,2cqi,28px);max-width:32em}
@container vngt (min-width:1024px){
	.vngt-root .vngt-identity{grid-template-columns:minmax(0,6fr) minmax(0,5fr);column-gap:clamp(40px,6vw,96px);column-gap:clamp(40px,6cqi,96px);align-items:start}
	.vngt-root .vngt-wordmark{grid-column:1 / -1}
	.vngt-root .vngt-expansion{grid-column:1;margin-top:.4em}
	.vngt-root .vngt-identity__lead{grid-column:2;grid-row:2;margin-top:.5em}
}
.vngt-root .vngt-pillars{display:grid;grid-template-columns:minmax(0,1fr);border-top:1px solid var(--vngt-line-2)}
.vngt-root .vngt-pillars__item{display:grid;grid-template-columns:auto 1fr;column-gap:16px;row-gap:4px;padding:22px 0;border-bottom:1px solid var(--vngt-line);min-width:0}
.vngt-root .vngt-pillars__num{grid-column:1;grid-row:1;font-family:var(--vngt-mono);font-size:.78em;color:var(--vngt-accent);padding-top:.25em}
.vngt-root .vngt-pillars__glyph{grid-column:1;grid-row:2 / span 2;width:40px;height:40px;margin-top:4px}
.vngt-root .vngt-pillars__name{grid-column:2;grid-row:1;font-size:1.08em;font-weight:600;letter-spacing:.08em;text-transform:uppercase;color:var(--vngt-text)}
.vngt-root .vngt-pillars__text{grid-column:2;grid-row:2;color:var(--vngt-text-2);font-size:.95em;max-width:30em}
@container vngt (min-width:768px){
	.vngt-root .vngt-pillars{grid-template-columns:repeat(2,minmax(0,1fr));column-gap:40px}
}
@container vngt (min-width:1024px){
	.vngt-root .vngt-pillars{grid-template-columns:repeat(4,minmax(0,1fr));column-gap:32px;border-top:0}
	.vngt-root .vngt-pillars__item{grid-template-columns:1fr;row-gap:14px;padding:22px 0 0;border-bottom:0;border-top:1px solid var(--vngt-line-2);align-content:start}
	.vngt-root .vngt-pillars__num,.vngt-root .vngt-pillars__glyph,.vngt-root .vngt-pillars__name,.vngt-root .vngt-pillars__text{grid-column:1;grid-row:auto}
	.vngt-root .vngt-pillars__glyph{width:48px;height:48px;margin:4px 0}
}

/* ---- 5.7 Scene 03 — nexus ----------------------------------------------- */
.vngt-root .vngt-nexus{display:grid;gap:40px}
.vngt-root .vngt-nexus__stage{position:relative;display:grid;grid-template-columns:minmax(0,1fr) 40px minmax(0,1fr);row-gap:14px;align-items:center;padding-top:8px}
.vngt-root .vngt-nexus__stage::before{content:"";position:absolute;left:50%;top:24px;bottom:22px;width:1px;background:var(--vngt-line-2);transform:translateX(-.5px)}
.vngt-root .vngt-nexus__svg{display:none}
.vngt-root .vngt-nexus__core{position:relative;grid-column:1 / -1;justify-self:center;display:inline-block;padding:.5em 1.1em;margin-bottom:6px;border:1px solid var(--vngt-accent-2);border-radius:999px;background:var(--vngt-bg);font-family:var(--vngt-mono);font-size:.8em;letter-spacing:.24em;color:var(--vngt-accent)}
.vngt-root .vngt-nexus__node{position:relative;z-index:1;display:flex;align-items:center;justify-content:center;min-height:48px;padding:.55em .9em;border:1px solid var(--vngt-line-2);border-radius:999px;background:var(--vngt-bg);color:var(--vngt-text);font-size:.92em;font-weight:500;line-height:1.2;text-align:center;transition:border-color .25s ease,color .25s ease,background-color .25s ease}
.vngt-root .vngt-nexus__bridge{position:relative;display:block;height:48px}
.vngt-root .vngt-nexus__bridge::before{content:"";position:absolute;left:-2px;right:-2px;top:50%;height:1px;background:var(--vngt-line-2);transition:background-color .25s ease}
.vngt-root .vngt-nexus__bridge::after{content:"";position:absolute;left:50%;top:50%;width:9px;height:9px;margin:-4.5px 0 0 -4.5px;border-radius:50%;background:var(--vngt-bg);border:1px solid var(--vngt-line-3);transition:background-color .25s ease,border-color .25s ease}
.vngt-root .vngt-nexus.is-enhanced .vngt-nexus__node.is-dim{color:var(--vngt-muted);border-color:var(--vngt-line)}
.vngt-root .vngt-nexus__node.is-on{border-color:var(--vngt-accent);color:var(--vngt-text)}
.vngt-root .vngt-nexus__node.is-focus{background:var(--vngt-accent-soft)}
.vngt-root .vngt-nexus__node[aria-pressed="true"]{background:var(--vngt-accent-soft);border-color:var(--vngt-accent)}
.vngt-root .vngt-nexus__node:hover{border-color:var(--vngt-accent)}
.vngt-root .vngt-nexus__bridge.is-on::before{background:var(--vngt-accent)}
.vngt-root .vngt-nexus__bridge.is-on::after{background:var(--vngt-accent);border-color:var(--vngt-accent)}
.vngt-root .vngt-nexus__ring-note{margin-top:20px;max-width:34em}
.vngt-root .vngt-nexus__ring{fill:none;stroke:var(--vngt-line);stroke-width:1;stroke-dasharray:2 6}
.vngt-root .vngt-nexus__tick{stroke:var(--vngt-line-3);stroke-width:1.2}
.vngt-root .vngt-nexus__orbit{fill:none;stroke:var(--vngt-line);stroke-width:1}
.vngt-root .vngt-nexus__link{fill:none;stroke:var(--vngt-line-3);stroke-width:1.4;transition:stroke .3s ease,stroke-width .3s ease,opacity .3s ease}
.vngt-root .vngt-nexus.is-enhanced .vngt-nexus__link{opacity:.5}
.vngt-root .vngt-nexus.is-enhanced .vngt-nexus__link.is-on{stroke:var(--vngt-accent);stroke-width:2.2;opacity:1}
.vngt-root .vngt-nexus__hub{fill:var(--vngt-bg);stroke:var(--vngt-accent-2);stroke-width:1.2}
@container vngt (min-width:768px){
	.vngt-root .vngt-nexus__stage{display:block;width:100%;max-width:580px;margin:0 auto;aspect-ratio:1 / 1;padding:0}
	@supports not (aspect-ratio:1 / 1){.vngt-root .vngt-nexus__stage{height:0;padding-bottom:min(100%,580px)}}
	.vngt-root .vngt-nexus__stage::before{display:none}
	.vngt-root .vngt-nexus__svg{display:block;position:absolute;left:0;top:0;width:100%;height:100%}
	.vngt-root .vngt-nexus__core{position:absolute;left:50%;top:50%;margin:0;padding:0;border:0;background:none;transform:translate(-50%,-50%);font-size:.78em}
	.vngt-root .vngt-nexus__node{position:absolute;left:var(--vngt-x);top:var(--vngt-y);transform:translate(-50%,-50%);min-width:7.4em;white-space:nowrap}
	.vngt-root .vngt-nexus__bridge{display:none}
}
@container vngt (min-width:1024px){
	.vngt-root .vngt-nexus{grid-template-columns:minmax(0,7fr) minmax(0,5fr);column-gap:clamp(40px,5vw,80px);column-gap:clamp(40px,5cqi,80px);align-items:center}
	.vngt-root .vngt-nexus__stage{margin:0}
}
.vngt-root .vngt-nexus__hint{font-size:.84em;color:var(--vngt-muted);margin-bottom:14px}
.vngt-root .vngt-nexus__rels{display:grid;gap:0;border-top:1px solid var(--vngt-line-2)}
.vngt-root .vngt-nexus__rel{padding:16px 0;border-bottom:1px solid var(--vngt-line)}
.vngt-root .vngt-nexus__rel-name{font-weight:600;color:var(--vngt-text);margin-bottom:.3em}
.vngt-root .vngt-nexus__rel-name span[aria-hidden]{color:var(--vngt-accent);padding:0 .15em}
.vngt-root .vngt-nexus__rel-text{color:var(--vngt-text-2)}
.vngt-root .vngt-nexus.is-enhanced .vngt-nexus__rel{display:none}
.vngt-root .vngt-nexus.is-enhanced .vngt-nexus__rel.is-on{display:block;min-height:7.2em}
.vngt-root .vngt-nexus.is-enhanced .vngt-nexus__rel-name{font-size:1.18em}
.vngt-root .vngt-question{margin-top:clamp(32px,4vw,48px);margin-top:clamp(32px,4cqi,48px)}
.vngt-root .vngt-question__label{font-size:.76em;font-weight:600;letter-spacing:.12em;text-transform:uppercase;color:var(--vngt-muted);margin-bottom:.8em}
.vngt-root .vngt-question__text{font-size:1.14em;color:var(--vngt-text);margin-bottom:.9em}
.vngt-root .vngt-question__branches{position:relative;display:grid;gap:12px;padding-left:22px}
.vngt-root .vngt-question__branches::before{content:"";position:absolute;left:4px;top:.6em;bottom:.9em;width:1px;background:var(--vngt-line-2)}
.vngt-root .vngt-question__branch{position:relative;display:grid;gap:2px;font-size:.92em}
.vngt-root .vngt-question__branch::before{content:"";position:absolute;left:-18px;top:.72em;width:12px;height:1px;background:var(--vngt-line-3)}
.vngt-root .vngt-question__kind{font-size:.8em;font-weight:600;letter-spacing:.1em;text-transform:uppercase;color:var(--vngt-accent)}
.vngt-root .vngt-question__desc{color:var(--vngt-text-2)}

/* ---- 5.8 Scene 04 — method + lab ---------------------------------------- */
.vngt-root .vngt-steps{display:grid;grid-template-columns:minmax(0,1fr);margin-bottom:clamp(40px,6vw,72px);margin-bottom:clamp(40px,6cqi,72px);counter-reset:none}
.vngt-root .vngt-steps__item{position:relative;display:grid;align-content:start;grid-template-columns:2.6em 1fr;column-gap:12px;padding:14px 0;border-top:1px solid var(--vngt-line)}
.vngt-root .vngt-steps__num{grid-row:1 / span 2;font-family:var(--vngt-mono);font-size:.8em;color:var(--vngt-accent);padding-top:.2em}
.vngt-root .vngt-steps__name{font-weight:600;letter-spacing:.1em;text-transform:uppercase;font-size:.9em;color:var(--vngt-text)}
.vngt-root .vngt-steps__desc{font-size:.9em;color:var(--vngt-text-2)}
@container vngt (min-width:768px){
	.vngt-root .vngt-steps{grid-template-columns:repeat(5,minmax(0,1fr));column-gap:0}
	.vngt-root .vngt-steps__item{grid-template-columns:1fr;row-gap:8px;padding:18px 22px 0 0;border-top:1px solid var(--vngt-line-2)}
	.vngt-root .vngt-steps__num{grid-row:auto}
	.vngt-root .vngt-steps__item::after{content:"→";position:absolute;right:10px;top:16px;color:var(--vngt-accent);font-size:.9em}
	.vngt-root .vngt-steps__item:last-child::after{content:none}
}
.vngt-root .vngt-lab{border:1px solid var(--vngt-line-2);background:var(--vngt-surface);border-radius:3px;padding:clamp(18px,3vw,36px);padding:clamp(18px,3cqi,36px)}
.vngt-root .vngt-lab__head{display:grid;gap:4px;margin-bottom:clamp(16px,2.4vw,28px);margin-bottom:clamp(16px,2.4cqi,28px);padding-bottom:16px;border-bottom:1px solid var(--vngt-line)}
.vngt-root .vngt-lab__title{font-size:clamp(1.2em,1em + .8vw,1.6em);font-size:clamp(1.2em,1em + .8cqi,1.6em);font-weight:500;color:var(--vngt-text)}
.vngt-root .vngt-lab__grid{display:grid;gap:28px}
.vngt-root .vngt-lab__fig{min-width:0}
.vngt-root .vngt-svg--lab{width:100%;max-width:680px;margin:0 auto}
.vngt-root .vngt-legend{display:grid;gap:8px;margin-top:16px;font-size:.82em;color:var(--vngt-muted)}
.vngt-root .vngt-legend__item{display:flex;align-items:center;gap:10px}
.vngt-root .vngt-legend__swatch{flex:none;display:inline-block;width:28px;height:12px;position:relative}
.vngt-root .vngt-legend__swatch::before{content:"";position:absolute;left:0;right:0;top:50%;height:0;border-top:1.6px solid var(--vngt-text)}
.vngt-root .vngt-legend__swatch--ref::before{border-top:1.6px dashed var(--vngt-line-3)}
.vngt-root .vngt-legend__swatch--tick::before{border:0;left:4px;right:auto;width:6px;height:6px;margin-top:-3px;border-radius:50%;background:var(--vngt-text-2);box-shadow:9px 0 0 var(--vngt-text-2),18px 0 0 var(--vngt-text-2)}
.vngt-root .vngt-legend__swatch--body::before{border:0;left:8px;right:auto;width:11px;height:11px;margin-top:-5.5px;border-radius:50%;background:var(--vngt-accent)}
.vngt-root .vngt-legend__swatch--vel::before{right:6px}
.vngt-root .vngt-legend__swatch--vel::after{content:"";position:absolute;right:1px;top:50%;margin-top:-4px;border-left:7px solid var(--vngt-text);border-top:4px solid transparent;border-bottom:4px solid transparent}
@container vngt (min-width:600px){.vngt-root .vngt-legend{grid-template-columns:repeat(2,minmax(0,1fr));column-gap:24px}}
@container vngt (min-width:1024px){
	.vngt-root .vngt-lab__grid{grid-template-columns:minmax(0,7fr) minmax(0,5fr);column-gap:clamp(32px,4vw,56px);column-gap:clamp(32px,4cqi,56px);align-items:start}
}
.vngt-root .vngt-lab__side{display:grid;gap:22px;align-content:start;min-width:0}
.vngt-root .vngt-lab__controls{display:grid;gap:6px}
.vngt-root .vngt-lab__slider-head{display:flex;align-items:baseline;justify-content:space-between;gap:12px}
.vngt-root .vngt-lab__label{font-weight:600;color:var(--vngt-text)}
.vngt-root .vngt-lab__value{font-family:var(--vngt-mono);font-size:1.15em;color:var(--vngt-accent);white-space:nowrap}
.vngt-root .vngt-lab__value i{font-family:var(--vngt-font)}
.vngt-root .vngt-lab__scale{display:flex;justify-content:space-between;font-size:.74em;color:var(--vngt-muted);letter-spacing:.04em}
.vngt-root .vngt-lab__help{font-size:.84em;color:var(--vngt-muted);margin-top:6px}
.vngt-root .vngt-lab__buttons{display:flex;flex-wrap:wrap;gap:10px;margin-top:10px}
.vngt-root .vngt-btn{display:inline-flex;align-items:center;justify-content:center;min-height:44px;padding:.55em 1.05em;border:1px solid var(--vngt-line-3);border-radius:2px;background:transparent;color:var(--vngt-text);font-size:.88em;font-weight:500;line-height:1.25;text-align:center;transition:border-color .2s ease,background-color .2s ease}
.vngt-root .vngt-btn:hover{border-color:var(--vngt-accent);background:var(--vngt-accent-soft)}
.vngt-root .vngt-btn--quiet{border-color:var(--vngt-line-2);color:var(--vngt-text-2)}
.vngt-root .vngt-lab__nojs{font-size:.88em;color:var(--vngt-text-2);padding:12px 14px;border-left:2px solid var(--vngt-accent)}
.vngt-root .vngt-lab.is-enhanced .vngt-lab__nojs{display:none}
.vngt-root .vngt-range{-webkit-appearance:none;appearance:none;display:block;width:100%;height:44px;margin:0;padding:0;background:transparent;border:0;cursor:pointer;--vngt-pct:58.8%}
.vngt-root .vngt-range::-webkit-slider-runnable-track{height:3px;border-radius:2px;background:linear-gradient(90deg,var(--vngt-accent) 0,var(--vngt-accent) var(--vngt-pct),var(--vngt-line-2) var(--vngt-pct),var(--vngt-line-2) 100%)}
.vngt-root .vngt-range::-webkit-slider-thumb{-webkit-appearance:none;appearance:none;width:26px;height:26px;margin-top:-11.5px;border-radius:50%;background:var(--vngt-bg);border:2px solid var(--vngt-accent);box-shadow:0 0 0 4px rgba(11,12,13,.9)}
.vngt-root .vngt-range::-moz-range-track{height:3px;border-radius:2px;background:var(--vngt-line-2)}
.vngt-root .vngt-range::-moz-range-progress{height:3px;border-radius:2px;background:var(--vngt-accent)}
.vngt-root .vngt-range::-moz-range-thumb{width:22px;height:22px;border-radius:50%;background:var(--vngt-bg);border:2px solid var(--vngt-accent)}
.vngt-root .vngt-range:focus-visible{outline:2px solid var(--vngt-focus);outline-offset:2px}
.vngt-root .vngt-readout{display:grid;border-top:1px solid var(--vngt-line-2)}
.vngt-root .vngt-readout__row{display:flex;align-items:baseline;justify-content:space-between;gap:16px;padding:10px 0;border-bottom:1px solid var(--vngt-line);font-size:.88em}
.vngt-root .vngt-readout__row dt{color:var(--vngt-text-2);min-width:0}
.vngt-root .vngt-readout__row dd{font-family:var(--vngt-mono);color:var(--vngt-text);white-space:nowrap}
.vngt-root .vngt-readout__row dd i{font-family:var(--vngt-font)}
.vngt-root .vngt-readout__row--key dt{color:var(--vngt-text)}
.vngt-root .vngt-readout__row--key dd{color:var(--vngt-accent)}
.vngt-root .vngt-lab__verify{font-size:.92em;color:var(--vngt-text-2)}

/* ---- 5.9 Scene 05 — landscape ------------------------------------------- */
.vngt-root .vngt-map__grid{display:grid;gap:36px}
.vngt-root .vngt-map__canvas{display:none;min-width:0}
.vngt-root .vngt-map__svg{width:100%;height:auto}
.vngt-root .vngt-map__note{margin-top:14px;max-width:44em}
.vngt-root .vngt-map__edge{fill:none;stroke:var(--vngt-line-2);stroke-width:1.2}
.vngt-root .vngt-map__thread{fill:none;stroke:var(--vngt-accent-2);stroke-width:1.6;stroke-dasharray:6 7;opacity:.55;transition:opacity .35s ease,stroke-width .35s ease}
.vngt-root .vngt-map.is-enhanced .vngt-map__thread{opacity:0}
.vngt-root .vngt-map.is-enhanced .vngt-map__thread.is-on{opacity:1;stroke:var(--vngt-accent);stroke-width:2.6;stroke-dasharray:none}
.vngt-root .vngt-map__dot{fill:var(--vngt-bg);stroke:var(--vngt-text);stroke-width:1.6;transition:fill .3s ease,stroke .3s ease}
.vngt-root .vngt-map__halo{fill:none;stroke:none;transition:stroke .3s ease}
.vngt-root .vngt-map__label{fill:var(--vngt-text);font-family:var(--vngt-font);font-size:21px;font-weight:500;paint-order:stroke;stroke:var(--vngt-bg);stroke-width:7px;stroke-linejoin:round;transition:fill .3s ease}
.vngt-root .vngt-map.has-thread .vngt-map__label{fill:var(--vngt-muted)}
.vngt-root .vngt-map__node.is-on .vngt-map__dot{fill:var(--vngt-accent);stroke:var(--vngt-accent)}
.vngt-root .vngt-map__node.is-on .vngt-map__halo{stroke:var(--vngt-accent-2)}
.vngt-root .vngt-map.has-thread .vngt-map__node.is-on .vngt-map__label{fill:var(--vngt-text)}
@container vngt (min-width:768px){.vngt-root .vngt-map__canvas{display:block}}
@container vngt (min-width:1280px){.vngt-root .vngt-map__grid{grid-template-columns:minmax(0,8fr) minmax(0,4fr);column-gap:56px;align-items:start}}
.vngt-root .vngt-threads__hint{margin:-.4em 0 14px}
.vngt-root .vngt-threads__list{display:grid;border-top:1px solid var(--vngt-line-2)}
.vngt-root .vngt-thread{border-bottom:1px solid var(--vngt-line)}
.vngt-root .vngt-thread__btn{display:flex;align-items:center;justify-content:space-between;gap:12px;width:100%;min-height:48px;padding:10px 0;font-size:1.02em;font-weight:600;color:var(--vngt-text);text-align:left}
.vngt-root .vngt-thread__btn:hover .vngt-thread__name{color:var(--vngt-accent)}
.vngt-root .vngt-thread__state{flex:none;width:12px;height:12px;border:1.5px solid var(--vngt-line-3);border-radius:50%;transition:background-color .2s ease,border-color .2s ease}
.vngt-root .vngt-thread__btn[aria-pressed="true"] .vngt-thread__state{background:var(--vngt-accent);border-color:var(--vngt-accent)}
.vngt-root .vngt-thread__btn[aria-pressed="true"] .vngt-thread__name{color:var(--vngt-accent)}
.vngt-root .vngt-thread__body{padding:0 0 16px}
.vngt-root .vngt-chain{display:flex;flex-wrap:wrap;align-items:center;gap:4px 0;margin-bottom:8px;font-size:.84em;color:var(--vngt-text)}
.vngt-root .vngt-chain__step{display:inline-flex;align-items:center}
.vngt-root .vngt-chain__step+.vngt-chain__step::before{content:"→";display:inline-block;margin:0 .5em;color:var(--vngt-accent)}
.vngt-root .vngt-thread__text{font-size:.86em;color:var(--vngt-text-2)}
.vngt-root .vngt-map.is-enhanced .vngt-thread__btn:not([aria-pressed="true"])+.vngt-thread__body .vngt-thread__text{color:var(--vngt-muted)}
.vngt-root .vngt-domains-wrap{margin-top:clamp(40px,6vw,72px);margin-top:clamp(40px,6cqi,72px)}
.vngt-root .vngt-domains{display:grid;grid-template-columns:minmax(0,1fr);border-top:1px solid var(--vngt-line-2)}
.vngt-root .vngt-domain{position:relative;display:grid;align-content:start;grid-template-columns:2.4em minmax(0,1fr);column-gap:10px;padding:14px 0;border-bottom:1px solid var(--vngt-line);transition:background-color .3s ease}
.vngt-root .vngt-domain__num{grid-row:1 / span 2;font-family:var(--vngt-mono);font-size:.78em;color:var(--vngt-muted);padding-top:.25em}
.vngt-root .vngt-domain__name{font-size:1em;font-weight:600;color:var(--vngt-text)}
.vngt-root .vngt-domain__text{grid-column:2;font-size:.88em;color:var(--vngt-text-2)}
.vngt-root .vngt-domain__order{position:absolute;right:0;top:14px;font-family:var(--vngt-mono);font-size:.78em;color:var(--vngt-accent)}
.vngt-root .vngt-domain.is-on{background:linear-gradient(90deg,var(--vngt-accent-soft),rgba(212,169,87,0) 70%)}
.vngt-root .vngt-domain.is-on .vngt-domain__num{color:var(--vngt-accent)}
@container vngt (min-width:768px){
	.vngt-root .vngt-domains{grid-template-columns:repeat(2,minmax(0,1fr));column-gap:40px}
}
@container vngt (min-width:1024px){
	.vngt-root .vngt-domains{grid-template-columns:repeat(4,minmax(0,1fr));column-gap:28px}
	.vngt-root .vngt-domain{grid-template-columns:1fr;row-gap:6px;padding:16px 10px 18px 0}
	.vngt-root .vngt-domain__num{grid-row:auto}
	.vngt-root .vngt-domain__text{grid-column:1}
}
.vngt-root .vngt-layers{margin-top:clamp(48px,7vw,96px);margin-top:clamp(48px,7cqi,96px)}
.vngt-root .vngt-layers__list{display:grid;grid-template-columns:minmax(0,1fr);gap:6px}
.vngt-root .vngt-layers__item{position:relative;display:grid;gap:2px;padding:14px 16px 14px 18px;border-left:2px solid rgba(212,169,87,calc(.25 + var(--vngt-depth,0) * .25));background:rgba(238,237,232,calc(.02 + var(--vngt-depth,0) * .02))}
.vngt-root .vngt-layers__name{font-weight:600;color:var(--vngt-text)}
.vngt-root .vngt-layers__desc{font-size:.84em;color:var(--vngt-muted)}
@container vngt (min-width:768px){
	.vngt-root .vngt-layers__list{grid-template-columns:repeat(4,minmax(0,1fr));gap:4px}
	.vngt-root .vngt-layers__item{border-left:0;border-top:2px solid rgba(212,169,87,calc(.25 + var(--vngt-depth,0) * .25));padding:16px 16px 18px}
	.vngt-root .vngt-layers__item::after{content:"";position:absolute;right:-4px;top:-2px;width:4px;height:2px;background:var(--vngt-bg)}
}

/* ---- 5.10 Scene 06 — interaction ---------------------------------------- */
.vngt-root .vngt-interaction__head{display:grid;gap:22px;margin-bottom:clamp(40px,6vw,72px);margin-bottom:clamp(40px,6cqi,72px)}
.vngt-root .vngt-interaction__lead{max-width:34em;color:var(--vngt-text-2)}
.vngt-root .vngt-loop{margin-bottom:clamp(56px,8vw,112px);margin-bottom:clamp(56px,8cqi,112px)}
.vngt-root .vngt-loop__list{position:relative;display:grid;gap:0;padding-left:0}
.vngt-root .vngt-loop__item{position:relative;display:flex;align-items:center;gap:14px;min-height:52px;padding:6px 0}
.vngt-root .vngt-loop__item::before{content:"";position:absolute;left:17px;top:0;bottom:0;width:1px;background:var(--vngt-line-2)}
.vngt-root .vngt-loop__item:first-child::before{top:50%}
.vngt-root .vngt-loop__item:last-child::before{bottom:50%}
.vngt-root .vngt-loop__num{position:relative;z-index:1;flex:none;display:inline-flex;align-items:center;justify-content:center;width:35px;height:35px;border:1px solid var(--vngt-line-3);border-radius:50%;background:var(--vngt-bg);font-family:var(--vngt-mono);font-size:.78em;color:var(--vngt-accent)}
.vngt-root .vngt-loop__name{font-size:1.05em;font-weight:500;color:var(--vngt-text)}
.vngt-root .vngt-loop__note{display:flex;align-items:flex-start;gap:12px;margin-top:16px;font-size:.88em;color:var(--vngt-muted);max-width:36em}
.vngt-root .vngt-loop__return{flex:none;color:var(--vngt-accent);font-size:1.4em;line-height:1;width:35px;text-align:center}
@container vngt (min-width:768px){
	.vngt-root .vngt-loop__list{grid-template-columns:repeat(5,minmax(0,1fr))}
	.vngt-root .vngt-loop__item{flex-direction:column;align-items:flex-start;gap:14px;padding:0 16px 0 0}
	.vngt-root .vngt-loop__item::before{left:0;right:0;top:17px;bottom:auto;width:auto;height:1px}
	.vngt-root .vngt-loop__item:first-child::before{top:17px;left:17px}
	.vngt-root .vngt-loop__item:last-child::before{bottom:auto;right:auto;width:17px}
	.vngt-root .vngt-loop__note{margin-top:28px;padding-top:16px;border-top:1px dashed var(--vngt-line-2)}
}
.vngt-root .vngt-station__intro{display:grid;gap:18px;margin-bottom:clamp(36px,5vw,60px);margin-bottom:clamp(36px,5cqi,60px)}
.vngt-root .vngt-station__title{font-size:clamp(1.4em,1.1em + 1vw,2em);font-size:clamp(1.4em,1.1em + 1cqi,2em);font-weight:500;color:var(--vngt-text)}
.vngt-root .vngt-station__body{color:var(--vngt-text-2);max-width:40em}
.vngt-root .vngt-notice{display:grid;gap:8px;max-width:46em;padding:14px 16px;border:1px solid var(--vngt-line-2);border-left:2px solid var(--vngt-accent);font-size:.86em;color:var(--vngt-text-2)}
.vngt-root .vngt-notice__tag{font-size:max(12px,.82em);font-weight:600;letter-spacing:.14em;text-transform:uppercase;color:var(--vngt-accent)}
@container vngt (min-width:1024px){
	.vngt-root .vngt-station__intro{grid-template-columns:minmax(0,4fr) minmax(0,7fr);column-gap:clamp(40px,5vw,80px);column-gap:clamp(40px,5cqi,80px);align-items:start}
	.vngt-root .vngt-station__body,.vngt-root .vngt-notice{grid-column:2}
	.vngt-root .vngt-station__title{grid-row:1 / span 2}
}

/* ---- 5.11 Scene 07 — integrity (tĩnh) ----------------------------------- */
.vngt-root .vngt-integrity{display:grid;gap:40px;margin-bottom:clamp(56px,8vw,104px);margin-bottom:clamp(56px,8cqi,104px)}
.vngt-root .vngt-integrity__fig{min-width:0}
.vngt-root .vngt-svg--chart{margin:6px 0 14px}
@container vngt (min-width:1024px){.vngt-root .vngt-integrity{grid-template-columns:minmax(0,6fr) minmax(0,5fr);column-gap:clamp(40px,5vw,80px);column-gap:clamp(40px,5cqi,80px);align-items:start}}
.vngt-root .vngt-taxo{display:grid;border-top:1px solid var(--vngt-line-2)}
.vngt-root .vngt-taxo__row{display:grid;gap:4px;padding:14px 0;border-bottom:1px solid var(--vngt-line)}
.vngt-root .vngt-taxo__term{display:flex;flex-wrap:wrap;align-items:center;gap:6px 12px}
.vngt-root .vngt-taxo__num{display:inline-flex;align-items:center;justify-content:center;width:22px;height:22px;border:1px solid var(--vngt-line-3);border-radius:50%;font-family:var(--vngt-mono);font-size:max(12px,.7em);color:var(--vngt-text)}
.vngt-root .vngt-taxo__glyph{position:relative;display:inline-block;width:26px;height:16px}
.vngt-root .vngt-taxo__glyph::before,.vngt-root .vngt-taxo__glyph::after{content:"";position:absolute}
.vngt-root .vngt-taxo__glyph--obs::before{left:9px;top:3px;width:9px;height:9px;border-radius:50%;background:var(--vngt-text)}
.vngt-root .vngt-taxo__glyph--obs::after{left:13px;top:0;width:1px;height:16px;background:var(--vngt-text-2)}
.vngt-root .vngt-taxo__glyph--dat::before{left:8px;top:3px;width:10px;height:10px;border:1.4px solid var(--vngt-text);background:var(--vngt-surface)}
.vngt-root .vngt-taxo__glyph--sim::before{left:0;right:0;top:8px;border-top:2px dashed var(--vngt-text)}
.vngt-root .vngt-taxo__glyph--asm::before{left:12px;top:0;bottom:0;border-left:1.4px dotted var(--vngt-text-2)}
.vngt-root .vngt-taxo__glyph--asm::after{left:14px;right:0;top:0;bottom:0;background:repeating-linear-gradient(45deg,var(--vngt-line-2) 0 1px,transparent 1px 4px)}
.vngt-root .vngt-taxo__glyph--inf::before{left:0;top:3px;width:0;height:0;border-left:24px solid var(--vngt-accent-soft);border-top:5px solid transparent;border-bottom:5px solid transparent}
.vngt-root .vngt-taxo__glyph--inf::after{left:0;right:2px;top:7.5px;border-top:2px dotted var(--vngt-accent)}
.vngt-root .vngt-taxo__en{font-family:var(--vngt-mono);font-size:.82em;letter-spacing:.08em;color:var(--vngt-text)}
.vngt-root .vngt-taxo__vi{font-size:.86em;color:var(--vngt-muted)}
.vngt-root .vngt-taxo__def{color:var(--vngt-text-2);font-size:.92em;padding-left:34px}
.vngt-root .vngt-integrity__nuance{margin-top:20px;padding-left:14px;border-left:2px solid var(--vngt-line-3);font-size:.88em;color:var(--vngt-text-2)}
.vngt-root .vngt-prov{margin-bottom:clamp(56px,8vw,104px);margin-bottom:clamp(56px,8cqi,104px)}
.vngt-root .vngt-prov__head{display:grid;gap:4px;margin-bottom:22px}
.vngt-root .vngt-prov__intro{color:var(--vngt-text-2);max-width:44em}
.vngt-root .vngt-prov__cols{display:none}
.vngt-root .vngt-prov__list{display:grid;border-top:1px solid var(--vngt-line-2)}
.vngt-root .vngt-prov__row{display:grid;gap:6px;padding:16px 0;border-bottom:1px solid var(--vngt-line)}
.vngt-root .vngt-prov__field{display:flex;flex-wrap:wrap;align-items:baseline;gap:4px 12px}
.vngt-root .vngt-prov__en{font-family:var(--vngt-mono);font-size:.8em;letter-spacing:.06em;color:var(--vngt-accent)}
.vngt-root .vngt-prov__vi{font-size:.9em;font-weight:600;color:var(--vngt-text)}
.vngt-root .vngt-prov__need{font-size:.9em;color:var(--vngt-text-2)}
.vngt-root .vngt-prov__example{font-size:.9em;color:var(--vngt-text);padding-left:12px;border-left:1px solid var(--vngt-line-3)}
.vngt-root .vngt-prov__label{color:var(--vngt-muted)}
@container vngt (min-width:1024px){
	.vngt-root .vngt-prov__cols{display:grid;grid-template-columns:minmax(0,3fr) minmax(0,4fr) minmax(0,5fr);column-gap:32px;padding-bottom:10px;font-size:.72em;letter-spacing:.12em;text-transform:uppercase;color:var(--vngt-muted)}
	.vngt-root .vngt-prov__row{grid-template-columns:minmax(0,3fr) minmax(0,4fr) minmax(0,5fr);column-gap:32px;align-items:start}
	.vngt-root .vngt-prov__field{flex-direction:column;gap:2px}
	.vngt-root .vngt-prov__example{padding-left:0;border-left:0}
	.vngt-root .vngt-prov__label{position:absolute !important;width:1px !important;height:1px !important;overflow:hidden !important;clip:rect(0,0,0,0) !important;white-space:nowrap !important}
}
.vngt-root .vngt-integrity__close{display:grid;gap:18px;padding-top:clamp(28px,4vw,48px);padding-top:clamp(28px,4cqi,48px);border-top:1px solid var(--vngt-line-2)}
.vngt-root .vngt-pull{font-size:clamp(1.25em,1em + 1vw,1.75em);font-size:clamp(1.25em,1em + 1cqi,1.75em);line-height:1.38;color:var(--vngt-text);max-width:28em;text-wrap:balance}
.vngt-root .vngt-integrity__figs{max-width:44em}

/* ---- 5.12 Scene 08 — standards (tĩnh) ----------------------------------- */
.vngt-root .vngt-status{display:grid;gap:8px;margin-top:22px;max-width:30em}
.vngt-root .vngt-status__tag{justify-self:start;padding:.3em .7em;border:1px solid var(--vngt-accent-2);border-radius:2px;font-size:.72em;font-weight:600;letter-spacing:.14em;text-transform:uppercase;color:var(--vngt-accent)}
.vngt-root .vngt-status__note{font-size:.86em;color:var(--vngt-muted)}
.vngt-root .vngt-std__cols{display:none}
.vngt-root .vngt-std__list{display:grid;border-top:1px solid var(--vngt-line-2)}
.vngt-root .vngt-std__item{display:grid;align-content:start;grid-template-columns:2.6em minmax(0,1fr);column-gap:10px;row-gap:4px;padding:16px 0;border-bottom:1px solid var(--vngt-line)}
.vngt-root .vngt-std__num{grid-row:1 / span 2;font-family:var(--vngt-mono);font-size:.78em;color:var(--vngt-accent);padding-top:.3em}
.vngt-root .vngt-std__name{font-size:1.06em;font-weight:600;color:var(--vngt-text)}
.vngt-root .vngt-std__text{grid-column:2;color:var(--vngt-text-2);font-size:.94em}
@container vngt (min-width:768px){
	.vngt-root .vngt-std__cols{display:grid;grid-template-columns:3.4em minmax(0,4fr) minmax(0,7fr);column-gap:24px;padding-bottom:10px;font-size:.72em;letter-spacing:.12em;text-transform:uppercase;color:var(--vngt-muted)}
	.vngt-root .vngt-std__item{grid-template-columns:2.6em minmax(0,4fr) minmax(0,7fr);column-gap:24px;align-items:baseline;padding:20px 0}
	.vngt-root .vngt-std__num{grid-row:auto}
	.vngt-root .vngt-std__text{grid-column:3}
}

/* ---- 5.13 Scene 09 — manifesto ------------------------------------------ */
.vngt-root .vngt-scene--manifesto{padding:clamp(96px,16vw,240px) 0;padding:clamp(96px,16cqi,240px) 0}
.vngt-root .vngt-manifesto{display:grid;gap:clamp(40px,6vw,72px);gap:clamp(40px,6cqi,72px)}
.vngt-root .vngt-manifesto__text{display:grid;gap:.7em;font-size:clamp(1.75em,.9em + 3.8vw,4.3em);font-size:clamp(1.75em,.9em + 3.8cqi,4.3em);font-weight:500;line-height:1.14;letter-spacing:.005em;text-transform:uppercase;color:var(--vngt-text)}
.vngt-root .vngt-manifesto__first{display:block;color:var(--vngt-muted)}
.vngt-root .vngt-manifesto__second{display:block;color:var(--vngt-text)}
.vngt-root .vngt-manifesto__line{display:block}
.vngt-root .vngt-cta{display:inline-flex;align-items:center;gap:.8em;min-height:52px;padding:.8em 1.5em;border:1px solid var(--vngt-accent);border-radius:2px;color:var(--vngt-text);font-size:.92em;font-weight:600;letter-spacing:.14em;text-transform:uppercase;transition:background-color .25s ease,color .25s ease}
.vngt-root .vngt-cta:hover{background:var(--vngt-accent);color:var(--vngt-bg)}
.vngt-root .vngt-cta__arrow{display:inline-block;letter-spacing:0;transition:transform .25s ease}
.vngt-root .vngt-cta:hover .vngt-cta__arrow{transform:translateX(4px)}

/* ---- 5.14 Motion (chỉ khi JS bật và người dùng không yêu cầu giảm chuyển động) */
.vngt-root.vngt-motion [data-vngt-reveal]{opacity:0;transform:translate3d(0,16px,0);transition:opacity .9s var(--vngt-ease),transform .9s var(--vngt-ease)}
.vngt-root.vngt-motion [data-vngt-reveal].is-in{opacity:1;transform:none}
.vngt-root.vngt-motion [data-vngt-draw]{stroke-dasharray:var(--vngt-len,0);stroke-dashoffset:var(--vngt-len,0);transition:stroke-dashoffset 1.8s cubic-bezier(.45,.05,.2,1) .15s}
.vngt-root.vngt-motion .is-in [data-vngt-draw]{stroke-dashoffset:0}
.vngt-root .is-animated [data-vngt-wedge="b"],.vngt-root .is-animated [data-vngt-static]{display:none}

/* ---- 7. ACCESSIBILITY SUPPORT (CSS) -------------------------------------- */
@media (prefers-reduced-motion:reduce){
	.vngt-root *,.vngt-root *::before,.vngt-root *::after{transition-duration:.01ms !important;transition-delay:0s !important;animation:none !important;scroll-behavior:auto !important}
	.vngt-root [data-vngt-reveal]{opacity:1 !important;transform:none !important}
	.vngt-root [data-vngt-draw]{stroke-dasharray:none !important;stroke-dashoffset:0 !important}
}
@media (forced-colors:active){
	.vngt-root .vngt-nexus__node,.vngt-root .vngt-btn,.vngt-root .vngt-cta{border:1px solid ButtonText}
	.vngt-root .vngt-nexus__node.is-on,.vngt-root .vngt-nexus__node[aria-pressed="true"],.vngt-root .vngt-thread__btn[aria-pressed="true"]{outline:2px solid Highlight}
}
@media print{
	.vngt-root{--vngt-bg:#fff;--vngt-surface:#fff;--vngt-surface-2:#f2f2f2;--vngt-text:#000;--vngt-text-2:#222;--vngt-muted:#444;--vngt-line:rgba(0,0,0,.15);--vngt-line-2:rgba(0,0,0,.3);--vngt-line-3:rgba(0,0,0,.55);--vngt-accent:#7a5a14;background:#fff;color:#000}
	.vngt-root [data-vngt-reveal]{opacity:1 !important;transform:none !important}
	.vngt-root .vngt-lab__controls{display:none !important}
}
CSS;
}

/**
 * Nén CSS đơn giản (bỏ comment, gộp khoảng trắng). An toàn với CSS của module vì không có chuỗi nhiều khoảng trắng.
 *
 * @param string $css CSS.
 * @return string
 */
function vnises_gt_minify_css( $css ) {
	$css = preg_replace( '!/\*.*?\*/!s', '', $css );
	$css = preg_replace( '/\s+/', ' ', $css );
	$css = preg_replace( '/\s*([{};,>])\s*/', '$1', $css );
	return trim( $css );
}

/* =============================================================================
 * 6. JAVASCRIPT
 * Vanilla ES5 trong một IIFE — không biến toàn cục, không thư viện, không request mạng.
 * Mọi instance được khởi tạo độc lập; khởi tạo lặp lại được chặn bằng data-vngt-ready.
 * ========================================================================== */

/**
 * @return string
 */
function vnises_gt_js() {
	return <<<'JS'
(function (window, document) {
	'use strict';
	if (!document.querySelectorAll || !window.addEventListener || !document.documentElement.classList) { return; }

	/* ---------- 6.1 Utilities ---------- */
	var docEl = document.documentElement;
	var mq = window.matchMedia ? window.matchMedia('(prefers-reduced-motion: reduce)') : null;
	var hasIO = typeof window.IntersectionObserver === 'function';
	var raf = window.requestAnimationFrame ? function (cb) { return window.requestAnimationFrame(cb); } : function (cb) { return window.setTimeout(function () { cb(Date.now()); }, 16); };
	var caf = window.cancelAnimationFrame ? function (id) { window.cancelAnimationFrame(id); } : function (id) { window.clearTimeout(id); };
	var loops = [];
	var motionHandlers = [];
	var TAU = Math.PI * 2;

	function reduced() { return !!(mq && mq.matches); }
	function each(list, fn) { for (var i = 0; i < list.length; i++) { fn(list[i], i); } }
	function r2(v) { return Math.round(v * 100) / 100; }
	function vn(v, d) { return v.toFixed(d).replace('.', ','); }
	function setAttrs(el, map) { for (var k in map) { if (Object.prototype.hasOwnProperty.call(map, k)) { el.setAttribute(k, typeof map[k] === 'number' ? String(r2(map[k])) : map[k]); } } }
	function toggle(el, cls, on) { if (on) { el.classList.add(cls); } else { el.classList.remove(cls); } }
	function num(el, name, fallback) { var v = parseFloat(el.getAttribute(name)); return isFinite(v) ? v : fallback; }
	function safe(fn, arg) { try { fn(arg); } catch (err) { /* Enhancement lỗi: nội dung server-render vẫn còn nguyên. */ } }

	/* Phương trình Kepler M = E − e·sin E (Newton–Raphson), 0 ≤ e < 1. */
	function keplerE(M, e) {
		var E = M + e * Math.sin(M), d, i;
		for (i = 0; i < 16; i++) {
			d = (E - e * Math.sin(E) - M) / (1 - e * Math.cos(E));
			E -= d;
			if (Math.abs(d) < 1e-9) { break; }
		}
		return E;
	}

	if (mq) {
		var onMq = function () { each(motionHandlers, function (fn) { safe(fn); }); each(loops, function (l) { l.update(); }); };
		if (mq.addEventListener) { mq.addEventListener('change', onMq); } else if (mq.addListener) { mq.addListener(onMq); }
	}
	document.addEventListener('visibilitychange', function () { each(loops, function (l) { l.update(); }); });

	/* Vòng lặp hoạt ảnh: chỉ chạy khi phần tử đang hiển thị, tab đang mở và không yêu cầu giảm chuyển động. */
	function makeLoop(target, step) {
		var loop = { visible: !hasIO, running: false, id: 0, last: 0 };
		function frame(t) {
			loop.id = 0;
			if (!loop.running) { return; }
			var dt = loop.last ? Math.min(t - loop.last, 50) : 0;
			loop.last = t;
			step(dt);
			loop.id = raf(frame);
		}
		loop.update = function () {
			var go = loop.visible && !document.hidden && !reduced();
			if (go && !loop.running) { loop.running = true; loop.last = 0; loop.id = raf(frame); }
			else if (!go && loop.running) { loop.running = false; if (loop.id) { caf(loop.id); } loop.id = 0; }
		};
		if (hasIO) {
			new window.IntersectionObserver(function (entries) {
				loop.visible = entries[entries.length - 1].isIntersecting;
				loop.update();
			}, { rootMargin: '60px 0px' }).observe(target);
		}
		loops.push(loop);
		return loop;
	}

	/* ---------- 6.2 Full-bleed (chỉ khi cột nội dung được căn giữa) ---------- */
	function initBleed(root) {
		if (root.getAttribute('data-vngt-bleed') !== '1') { return; }
		var pending = 0;
		function reset() {
			root.style.removeProperty('width');
			root.style.removeProperty('max-width');
			root.style.removeProperty('margin-left');
			root.style.removeProperty('margin-right');
			root.classList.remove('vngt-bleed');
		}
		function apply() {
			pending = 0;
			var parent = root.parentElement;
			if (!parent) { return; }
			reset();
			var vw = docEl.clientWidth;
			var pr = parent.getBoundingClientRect();
			var cs = window.getComputedStyle(parent);
			var left = pr.left + (parseFloat(cs.borderLeftWidth) || 0) + (parseFloat(cs.paddingLeft) || 0);
			var right = pr.right - (parseFloat(cs.borderRightWidth) || 0) - (parseFloat(cs.paddingRight) || 0);
			var gapL = left, gapR = vw - right;
			if (vw <= 0 || (gapL < 1 && gapR < 1)) { return; }
			if (Math.abs(gapL - gapR) > Math.max(24, vw * 0.03)) { return; } /* Cột lệch (sidebar): giữ trong cột. */
			root.style.setProperty('max-width', 'none', 'important');
			root.style.setProperty('width', vw + 'px', 'important');
			root.style.setProperty('margin-right', '0px', 'important');
			root.style.setProperty('margin-left', (-gapL) + 'px', 'important');
			var actual = root.getBoundingClientRect().left;
			if (Math.abs(actual) > 0.5) { root.style.setProperty('margin-left', (-gapL - actual) + 'px', 'important'); }
			root.classList.add('vngt-bleed');
		}
		apply();
		window.addEventListener('resize', function () { if (!pending) { pending = raf(apply); } }, { passive: true });
		window.addEventListener('load', apply);
	}

	/* ---------- 6.3 Reveal + line drawing ---------- */
	function initReveal(root) {
		if (!hasIO || reduced()) { return; }
		var items = root.querySelectorAll('[data-vngt-reveal]');
		if (!items.length) { return; }
		each(root.querySelectorAll('[data-vngt-draw]'), function (p) {
			if (typeof p.getTotalLength === 'function') {
				var len = p.getTotalLength();
				if (len > 0) { p.style.setProperty('--vngt-len', Math.ceil(len) + 'px'); }
			}
		});
		var io = new window.IntersectionObserver(function (entries) {
			each(entries, function (en) {
				if (en.isIntersecting) { en.target.classList.add('is-in'); io.unobserve(en.target); }
			});
		}, { rootMargin: '0px 0px -6% 0px', threshold: 0 });
		root.classList.add('vngt-motion');
		each(items, function (el) { io.observe(el); });
		motionHandlers.push(function () {
			if (reduced()) { root.classList.remove('vngt-motion'); each(items, function (el) { el.classList.add('is-in'); }); }
		});
	}

	/* ---------- 6.4 Hero orbit — định luật Kepler II ---------- */
	function initHeroOrbit(svg) {
		var a = num(svg, 'data-a', 120), e = num(svg, 'data-e', 0.6);
		var cx = num(svg, 'data-cx', 150), cy = num(svg, 'data-cy', 112);
		var dM = TAU * num(svg, 'data-dm', 0.08);
		var b = a * Math.sqrt(1 - e * e), fx = cx + a * e;
		var wedge = svg.querySelector('[data-vngt-wedge="a"]');
		var body = svg.querySelector('[data-vngt-body]');
		var radius = svg.querySelector('[data-vngt-radius]');
		if (!wedge || !body || !radius) { return; }
		var fig = svg.parentNode;
		var saved = { d: wedge.getAttribute('d'), cx: body.getAttribute('cx'), cy: body.getAttribute('cy') };
		var PERIOD = 16000, M = dM / 2;
		function pos(E) { return [cx + a * Math.cos(E), cy - b * Math.sin(E)]; }
		function draw() {
			var E1 = keplerE(M - dM, e), E2 = keplerE(M, e);
			var p1 = pos(E1), p2 = pos(E2);
			wedge.setAttribute('d', 'M' + r2(fx) + ' ' + r2(cy) + 'L' + r2(p1[0]) + ' ' + r2(p1[1]) + 'A' + a + ' ' + r2(b) + ' 0 ' + ((E2 - E1) > Math.PI ? 1 : 0) + ' 0 ' + r2(p2[0]) + ' ' + r2(p2[1]) + 'Z');
			setAttrs(body, { cx: p2[0], cy: p2[1] });
			setAttrs(radius, { x2: p2[0], y2: p2[1] });
		}
		var loop = makeLoop(svg, function (dt) { M = (M + TAU * dt / PERIOD) % TAU; draw(); });
		function mode() {
			var animate = !reduced();
			toggle(fig, 'is-animated', animate);
			if (!animate) {
				wedge.setAttribute('d', saved.d);
				body.setAttribute('cx', saved.cx); body.setAttribute('cy', saved.cy);
				radius.setAttribute('x2', saved.cx); radius.setAttribute('y2', saved.cy);
			} else { draw(); }
			loop.update();
		}
		motionHandlers.push(mode);
		mode();
	}

	/* ---------- 6.5 Nexus ---------- */
	function initNexus(el) {
		var nodes = el.querySelectorAll('[data-vngt-node]');
		var links = el.querySelectorAll('[data-vngt-link]');
		var bridges = el.querySelectorAll('.vngt-nexus__bridge');
		var rels = el.querySelectorAll('.vngt-nexus__rel');
		var stage = el.querySelector('.vngt-nexus__stage');
		var hint = el.querySelector('[data-vngt-hint]');
		if (!nodes.length || !stage) { return; }
		var selected = nodes[0];

		function pairOf(n) { return n.getAttribute('data-pair'); }
		function show(node) {
			var pair = pairOf(node);
			each(nodes, function (n) {
				var on = pairOf(n) === pair;
				toggle(n, 'is-on', on);
				toggle(n, 'is-dim', !on);
				toggle(n, 'is-focus', n === node);
			});
			each(links, function (l) { toggle(l, 'is-on', l.getAttribute('data-vngt-link') === pair); });
			each(bridges, function (b) { toggle(b, 'is-on', b.getAttribute('data-pair') === pair); });
			each(rels, function (r) { toggle(r, 'is-on', r.getAttribute('data-pair') === pair); });
		}
		function select(node) {
			selected = node;
			each(nodes, function (n) { n.setAttribute('aria-pressed', n === node ? 'true' : 'false'); });
			show(node);
		}
		each(nodes, function (n) {
			n.addEventListener('click', function () { select(n); });
			n.addEventListener('focus', function () { show(n); });
			n.addEventListener('pointerenter', function (ev) { if (ev.pointerType === 'mouse') { show(n); } });
		});
		stage.addEventListener('pointerleave', function (ev) { if (ev.pointerType === 'mouse' && !stage.contains(document.activeElement)) { show(selected); } });
		stage.addEventListener('focusout', function (ev) { if (!ev.relatedTarget || !stage.contains(ev.relatedTarget)) { show(selected); } });
		if (hint) { hint.hidden = false; }
		el.classList.add('is-enhanced');
		select(selected);
	}

	/* ---------- 6.6 Orbit lab — độ lệch tâm ---------- */
	function initLab(lab) {
		var svg = lab.querySelector('[data-vngt-lab-svg]');
		var input = lab.querySelector('[data-vngt-range]');
		var controls = lab.querySelector('[data-vngt-controls]');
		if (!svg || !input || !controls) { return; }
		var a = num(svg, 'data-a', 150), fx = num(svg, 'data-fx', 300), fy = num(svg, 'data-fy', 170);
		var E0 = num(lab, 'data-e0', 0.5), EMAX = num(input, 'max', 0.85);
		var cur = svg.querySelector('[data-vngt-cur]'), ref = svg.querySelector('[data-vngt-ref]');
		var ticks = svg.querySelectorAll('[data-vngt-ticks] circle');
		var f2 = svg.querySelector('[data-vngt-f2]'), f2l = svg.querySelector('[data-vngt-f2-label]');
		var peri = svg.querySelector('[data-vngt-peri-label]'), apo = svg.querySelector('[data-vngt-apo-label]');
		var body = svg.querySelector('[data-vngt-body]'), vel = svg.querySelector('[data-vngt-vel]');
		var out = {};
		each(lab.querySelectorAll('[data-vngt-out]'), function (o) { out[o.getAttribute('data-vngt-out')] = o; });
		var state = { e: E0, ref: 0, M: 0 };
		var PERIOD = 12000;

		function clampE(v) { v = parseFloat(v); if (!isFinite(v)) { v = E0; } return Math.min(EMAX, Math.max(0, v)); }
		function setText(key, text) { if (out[key]) { out[key].textContent = text; } }
		function setE(key, v) {
			if (!out[key]) { return; }
			out[key].textContent = '';
			var i = document.createElement('i'); i.textContent = 'e';
			out[key].appendChild(i);
			out[key].appendChild(document.createTextNode(' = ' + vn(v, 2)));
		}
		function place() {
			var e = state.e, b = a * Math.sqrt(1 - e * e), cx = fx - a * e;
			var E = keplerE(state.M, e);
			var px = cx + a * Math.cos(E), py = fy - b * Math.sin(E);
			var r = a * (1 - e * Math.cos(E));
			var speed = Math.sqrt(Math.max(0, 2 * a / r - 1));
			var tx = -a * Math.sin(E), ty = -b * Math.cos(E), tl = Math.sqrt(tx * tx + ty * ty) || 1;
			var L = 26 * speed;
			setAttrs(body, { cx: px, cy: py });
			setAttrs(vel, { x1: px, y1: py, x2: px + tx / tl * L, y2: py + ty / tl * L });
		}
		function render() {
			var e = state.e, b = a * Math.sqrt(1 - e * e), c = a * e, cx = fx - c;
			setAttrs(cur, { cx: cx, ry: b });
			setAttrs(ref, { cx: fx - a * state.ref, ry: a * Math.sqrt(1 - state.ref * state.ref) });
			each(ticks, function (t, k) {
				var E = keplerE(TAU * k / ticks.length, e);
				setAttrs(t, { cx: cx + a * Math.cos(E), cy: fy - b * Math.sin(E) });
			});
			var hide = e < 0.02;
			f2.style.display = hide ? 'none' : '';
			f2l.style.display = hide ? 'none' : '';
			setAttrs(f2, { cx: fx - 2 * c });
			setAttrs(f2l, { x: fx - 2 * c });
			setAttrs(peri, { x: cx + a - 8 });
			setAttrs(apo, { x: cx - a + 8 });
			setE('e', e); setE('cur', e); setE('ref', state.ref);
			setText('ba', vn(Math.sqrt(1 - e * e), 2));
			setText('rr', vn((1 - e) / (1 + e), 2));
			setText('vv', vn((1 + e) / (1 - e), 2));
			input.setAttribute('aria-valuetext', 'e = ' + vn(e, 2) + '; b/a = ' + vn(Math.sqrt(1 - e * e), 2) + '; tỉ số khoảng cách cận điểm/viễn điểm = ' + vn((1 - e) / (1 + e), 2));
			input.style.setProperty('--vngt-pct', (e / EMAX * 100).toFixed(2) + '%');
			place();
		}
		var loop = makeLoop(svg, function (dt) { state.M = (state.M + TAU * dt / PERIOD) % TAU; place(); });
		input.addEventListener('input', function () { state.e = clampE(input.value); render(); });
		input.addEventListener('change', function () { state.e = clampE(input.value); render(); });
		lab.querySelector('[data-vngt-pin]').addEventListener('click', function () { state.ref = state.e; render(); });
		lab.querySelector('[data-vngt-reset]').addEventListener('click', function () {
			state.e = E0; state.ref = 0; state.M = 0; input.value = String(E0); render();
		});
		motionHandlers.push(function () { if (reduced()) { state.M = 0; place(); } loop.update(); });
		controls.hidden = false;
		lab.classList.add('is-enhanced');
		state.e = clampE(input.value);
		render();
		loop.update();
	}

	/* ---------- 6.7 Science landscape ---------- */
	function initMap(el) {
		var buttons = el.querySelectorAll('[data-vngt-thread]');
		var paths = el.querySelectorAll('[data-vngt-thread-path]');
		var mapNodes = el.querySelectorAll('[data-vngt-map-node]');
		var domains = el.querySelectorAll('.vngt-domain');
		var threads = el.querySelectorAll('.vngt-thread');
		var hint = el.querySelector('[data-vngt-hint]');
		if (!buttons.length) { return; }
		var chains = [];
		each(threads, function (t) {
			var names = [];
			each(t.querySelectorAll('.vngt-chain__step'), function (s) { names.push(s.textContent); });
			chains.push(names);
		});
		var keyByName = {};
		each(domains, function (d) { var n = d.querySelector('.vngt-domain__name'); if (n) { keyByName[n.textContent] = d.getAttribute('data-domain'); } });
		function set(active) {
			var chainKeys = [];
			if (active >= 0 && chains[active]) { each(chains[active], function (n) { chainKeys.push(keyByName[n]); }); }
			each(buttons, function (b) { b.setAttribute('aria-pressed', String(parseInt(b.getAttribute('data-vngt-thread'), 10) === active)); });
			each(paths, function (p) { toggle(p, 'is-on', parseInt(p.getAttribute('data-vngt-thread-path'), 10) === active); });
			each(mapNodes, function (n) { toggle(n, 'is-on', chainKeys.indexOf(n.getAttribute('data-vngt-map-node')) > -1); });
			each(domains, function (d) {
				var idx = chainKeys.indexOf(d.getAttribute('data-domain'));
				toggle(d, 'is-on', idx > -1);
				var o = d.querySelector('[data-vngt-order]');
				if (o) { o.textContent = idx > -1 ? '→ ' + (idx + 1) + '/' + chainKeys.length : ''; }
			});
			toggle(el, 'has-thread', active >= 0);
		}
		var current = 0;
		each(buttons, function (b) {
			b.addEventListener('click', function () {
				var i = parseInt(b.getAttribute('data-vngt-thread'), 10);
				current = (current === i) ? -1 : i;
				set(current);
			});
		});
		if (hint) { hint.hidden = false; }
		el.classList.add('is-enhanced');
		set(current);
	}

	/* ---------- 8. INITIALIZATION / FALLBACK ---------- */
	function initRoot(root) {
		if (root.getAttribute('data-vngt-ready') === '1') { return; }
		root.setAttribute('data-vngt-ready', '1');
		root.classList.add('vngt-js');
		safe(initBleed, root);
		safe(initReveal, root);
		each(root.querySelectorAll('[data-vngt-hero-orbit]'), function (el) { safe(initHeroOrbit, el); });
		each(root.querySelectorAll('[data-vngt-nexus]'), function (el) { safe(initNexus, el); });
		each(root.querySelectorAll('[data-vngt-lab]'), function (el) { safe(initLab, el); });
		each(root.querySelectorAll('[data-vngt-map]'), function (el) { safe(initMap, el); });
	}
	function initAll() { each(document.querySelectorAll('[data-vngt-root]'), function (r) { safe(initRoot, r); }); }
	initAll();
	if (document.readyState === 'loading') { document.addEventListener('DOMContentLoaded', initAll); }
}(window, document));
JS;
}

/* =============================================================================
 * 7. ACCESSIBILITY SUPPORT
 * - Heading level cấu hình được (vnises_gt_sanitize_level) để không tạo H1 thừa.
 * - Mọi điều khiển là <button>/<input type="range">/<a> thật; focus luôn hiển thị.
 * - Hình có ý nghĩa: role="img" + aria-label/aria-labelledby + figcaption văn bản.
 * - Sơ đồ tương tác có textual equivalent đầy đủ trong HTML (quan hệ Nexus, chuỗi lĩnh vực).
 * - prefers-reduced-motion: dừng quỹ đạo, bỏ line drawing và reveal, giữ nguyên nội dung/chức năng.
 * ========================================================================== */

/* =============================================================================
 * 8. INITIALIZATION / FALLBACK
 * ========================================================================== */

/**
 * Trạng thái assets trên trang hiện tại ('css', 'js').
 *
 * @param string $key  Khóa asset.
 * @param bool   $mark true = đánh dấu đã in.
 * @return bool Asset đã được in hay chưa.
 */
function vnises_gt_asset_state( $key, $mark = false ) {
	static $state = array( 'css' => false, 'js' => false );
	if ( $mark ) {
		$state[ $key ] = true;
	}
	return ! empty( $state[ $key ] );
}

/**
 * Asset chỉ in một lần trên mỗi trang.
 * Lần render trước wp_head (ví dụ plugin SEO dựng mô tả từ nội dung) không "tiêu" cờ,
 * vì output đó thường bị bỏ đi — nếu đánh dấu, trang thật sẽ thiếu assets.
 *
 * @param string $key 'css' hoặc 'js'.
 * @return bool
 */
function vnises_gt_should_print_asset( $key ) {
	$in_body = did_action( 'wp_head' ) && ! doing_action( 'wp_head' );
	if ( ! $in_body ) {
		return true;
	}
	if ( vnises_gt_asset_state( $key ) ) {
		return false;
	}
	vnises_gt_asset_state( $key, true );
	return true;
}

/**
 * Trường hợp phổ biến (shortcode nằm trong nội dung bài/trang): đưa CSS lên <head> để HTML hợp lệ
 * và không nhấp nháy. Shortcode trong widget/page builder vẫn có fallback in CSS inline ở lần render đầu.
 */
function vnises_gt_enqueue_head_css() {
	if ( ! is_singular() ) {
		return;
	}
	$post = get_post();
	if ( ! $post || ! has_shortcode( (string) $post->post_content, 'vnises_gioithieu' ) ) {
		return;
	}
	wp_register_style( 'vnises-gt', false, array(), VNISES_GT_VERSION );
	wp_enqueue_style( 'vnises-gt' );
	wp_add_inline_style( 'vnises-gt', vnises_gt_minify_css( vnises_gt_css() ) );
	vnises_gt_asset_state( 'css', true );
}
add_action( 'wp_enqueue_scripts', 'vnises_gt_enqueue_head_css' );
