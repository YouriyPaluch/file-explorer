<?php

use YouriyPaluch\FileExplorer\DocumentEntity;

/** @var DocumentEntity[] $documentEntityList */
/** @var string $baseUrl */
/** @var array $breadcrumbs */
/** @var string $backUrl */
/** @var array $viewUrlList */
/** @var array $sortList */
/** @var string $content */
/** @var string $template */
/** @var int $countPage */
/** @var int $currentPage */
?>

<!DOCTYPE html>
<html lang="uk">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>File Manager</title>
	<style>
		/* --- Global variables(Light theme default) --- */
		:root {
			--bg-body: #EEF1F7;
			--bg-panel: #FFFFFF;
			--text-main: #111827;
			--text-secondary: #374151;
			--border-color: #C7CEDA;
			--hover-color: #D4E1FF;
			--btn-bg: #FFFFFF;
			--btn-hover: #DDE9FF;
			--btn-shadow-color: #6B8CFF;
			--folder-color: #2F5BFF;
			--file-bg: #EDEFF3;
			--theme-slider-background: #1F2937;
			--theme-slider-point-background: #FFFFFF;
			--thead-background: #D1D7E0;
			--row-background-odd: #F4F6FA;
			--row-background-even: #FFFFFF;
			--dropdown-background: #FFFFFF;
			--dropdown-hover: #D4E1FF;
			--dropdown-item-active-color: #5C8FFF;
			--current-page-color: #1F2937;
			--selected-checkbox-color: #1D4ED8;
			--sort-item-order-color: #FFFFFF;
			--sort-item-p-background: #C7CEDA;
		}

		[data-theme="dark"] {
			--bg-body: #1D1F23;
			--bg-panel: #25272C;
			--text-main: #ECECEC;
			--text-secondary: #A6A6A6;
			--border-color: #444444;
			--hover-color: #5C8FFF52;
			--btn-bg: #2F3238;
			--btn-hover: #424957;
			--folder-color: #3F8CB5;
			--file-bg: #444444;
			--theme-slider-background: #FFFFFF;
			--theme-slider-point-background: #333333;
			--thead-background: #16181C;
			--btn-shadow-color: #5C8FFFBF;
			--current-page-color: #5C8FFF;
			--selected-checkbox-color: #04FF00;
			--dropdown-background: #2C2F36;
			--dropdown-hover: #3A476F;
			--dropdown-item-active-color: #5C8FFF;
			--row-background-odd: #24262B;
			--row-background-even: #2A2C31;
			--sort-item-order-color: #69696936;
			--sort-item-p-background: #2C2F36;
		}

		/* Resetting basic styles */
		* {
			cursor: default;
			box-sizing: border-box;
			margin: 0;
			padding: 0;
		}

		body, html {
			font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
			background-color: var(--bg-body);
			color: var(--text-main);
			overflow: hidden; /* Disabling scrolling of the entire page */
		}

		.file-manager-container {
			display: flex;
			flex-direction: column;
			height: 100vh;
		}

		/* --- Header --- */
		.fe-header {
			background-color: var(--bg-panel);
			border-bottom: 1px solid var(--border-color);
			min-height: 50px;
			display: flex;
			align-items: center;
			padding: 0 15px;
			gap: 10px;
			flex-shrink: 0; /* Header should not shrink */
			box-shadow: 0 1px 3px rgba(0,0,0,0.05);
		}

		.fe-btn {
			background: var(--btn-bg);
			border: 1px solid var(--border-color);
			color: var(--text-main);
			border-radius: 6px;
			padding: 5px 12px;
			font-size: 13px;
			cursor: pointer;
			transition: all 0.3s ease;
		}

		/* --- Search (header) --- */
		.fe-search {
			display: flex;
			align-items: center;
			justify-content: flex-end;
			gap: 8px;
			position: relative;
			height: 26px;
		}

		.search-submit {
			top: 50%;
			display: inline-flex;
			position: absolute;
			right: 8px;
			transform: translateY(-50%);
			width: 26px;
			height: 26px;
			border-radius: 8px;
			padding: 0;
			border: none;
			cursor: pointer;
			color: #FFFFFF;
			align-items: center;
			justify-content: center;
			background: linear-gradient(135deg, #3b82f6, #6366f1);
			transition: background 0.2s ease, box-shadow 0.2s ease;
		}

		.search-submit:hover {
			background: linear-gradient(135deg, #2563eb, #4f46e5);
			box-shadow: 0 0 5px #4f46e5, 0 0 15px #2563eb;
		}

		.search-submit svg {
			width: 18px;
			height: 18px;
		}

		.search-clear-btn svg {
			fill: var(--selected-checkbox-color);
		}

		.search-submit *,
		.search-clear-btn * {
			cursor: pointer;
		}

		.search-form {
			display: inline-flex;
			align-items: center;
			gap: 8px;
		}

		.search-input {
			cursor: text;
			padding: 8px 10px;
			border: 1px solid var(--border-color);
			background: var(--bg-panel);
			color: var(--text-main);
			border-radius: 6px;
			outline: none;
		}

		.search-input::placeholder {
			color: var(--text-secondary);
			opacity: 0.8;
		}

		.search-input:-webkit-autofill {
			box-shadow: 0 0 0 1000px var(--bg-panel) inset;
			-webkit-text-fill-color: var(--text-main) !important;
			caret-color: var(--text-main);
		}

		/* make room for clear and submit buttons inside the input */
		.search-form .search-input {
			padding-right: 92px; /* space for two controls on the right */
		}

		.search-clear-btn {
			position: absolute;
			right: 45px;
			top: calc(50% - 8px);
			background: transparent;
			cursor: pointer;
		}

		/* utility */
		.hidden { display: none !important; }

		.fe-btn.back {
			padding: 1px 5px;
		}

		.fe-btn.back svg {
			fill: var(--text-main);
		}

		.fe-btn:hover {
			background: var(--btn-hover);
			border-color: var(--btn-shadow-color);
			box-shadow: 0 0 5px var(--btn-shadow-color), 0 0 15px var(--btn-shadow-color);
		}

		.fe-breadcrumbs {
			background: var(--bg-body);
			border: 1px solid var(--border-color);
			color: var(--text-secondary);
			flex-grow: 1;
			border-radius: 6px;
			padding: 5px 10px;
			font-size: 13px;
			display: flex;
			align-items: center;
			font-weight: 500;
			overflow-x: auto;
		}

		.fe-breadcrumbs::-webkit-scrollbar {
			height: 5px;
		}

		.fe-breadcrumbs::-webkit-scrollbar-thumb {
			background: #c1c1c1;
			border-radius: 5px;
		}
		.fe-breadcrumbs::-webkit-scrollbar-thumb:hover {
			background: #ffffff;
		}

		.current-dir {
			color: var(--current-page-color);
		}

		a * {
			cursor: pointer;
		}

		a {
			color: var(--text-main);
			text-decoration: none;
			cursor: pointer;
		}

		/* --- Main content --- */
		.fe-content {
			height: calc(100vh - 125px);
			padding: 20px 0;
			background-color: var(--bg-body);
		}

		.fe-content.tile {
			display: grid;
			grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
			grid-auto-rows: max-content;
			gap: 20px;
			overflow-x: hidden;
			overflow-y: auto;
		}

		.file-item {
			width: 110px;
			height: 160px;
			display: flex;
			flex-direction: column;
			align-items: center;
			padding: 10px;
			border-radius: 8px;
			cursor: pointer;
			transition: background-color 0.2s;
			text-align: center;
		}

		.file-item:hover {
			background-color: var(--hover-color);
		}

		.file-icon svg{
			width: 64px;
			height: 64px;
			margin-bottom: 8px;
		}

		.file-icon-svg {
			fill: var(--text-main);
			stroke: var(--text-main);
		}

		.file-name {
			font-size: 13px;
			color: var(--text-main);
			word-break: break-word;
			max-width: 100%;
			line-height: 1.3;
		}

		/* Customize scrollbar */
		.fe-content::-webkit-scrollbar {
			width: 10px;
		}

		tbody::-webkit-scrollbar {
			width: 5px;
		}

		tbody::-webkit-scrollbar-thumb,
		.fe-content::-webkit-scrollbar-thumb {
			background: #c1c1c1;
			border-radius: 5px;
		}

		tbody::-webkit-scrollbar-thumb:hover,
		.fe-content::-webkit-scrollbar-thumb:hover {
			background: #a8a8a8;
		}

		.theme-switcher-wrapper {
			display: flex;
			align-items: center;
			margin-right: 10px;
		}

		.theme-switcher {
			display: inline-block;
			height: 26px;
			position: relative;
			width: 50px;
		}

		.theme-switcher input {
			display: none; /* Hide default HTML checkbox */
		}

		.slider {
			background-color: var(--theme-slider-background);
			bottom: 0;
			cursor: pointer;
			left: 0;
			position: absolute;
			right: 0;
			top: 0;
			transition: .4s;
			border-radius: 34px;
			display: flex;
			align-items: center;
			justify-content: space-between;
			padding: 0 4px;
		}

		.slider:before {
			background-color: var(--theme-slider-point-background);
			bottom: 3px;
			content: "";
			height: 20px;
			left: 3px;
			position: absolute;
			transition: .4s;
			width: 20px;
			border-radius: 50%;
			z-index: 2;
		}

		input:checked + .slider:before {
			transform: translateX(24px);
		}

		.icon-sun, .icon-moon {
			width: 14px;
			height: 14px;
			z-index: 1;
		}

		.icon-sun {
			color: #000000;
		}

		.icon-moon {
			color: #FFFFFF;
		}

		/* --- Dropdown Menu Styles --- */
		.dropdown {
			cursor: pointer;
			position: relative;
			display: inline-block;
		}

		.fe-btn.view-toggle-btn {
			min-width: 100px;
		}

		.dropdown-content {
			display: none;
			position: absolute;
			right: 0;
			background-color: var(--dropdown-background);
			min-width: 100px;
			box-shadow: 0 8px 16px 0 rgba(0,0,0,0.2);
			border: 1px solid var(--border-color);
			border-radius: 6px;
			z-index: 100;
			overflow: hidden;
			text-align: center;
		}

		.dropdown-content a {
			color: var(--text-main);
			text-decoration: none;
			display: block;
			cursor: pointer;
		}

		.dropdown-content p:not(:first-child) {
			margin-top: 10px;
		}

		.dropdown-content a:hover {
			border-radius: 5px;
			background-color: var(--dropdown-hover);
		}

		.show {
			display: block;
		}

		.sort-item p {
			background-color: var(--sort-item-p-background);
			padding: 7px 0;
		}

		.sort-item-order {
			background-color: var(--sort-item-order-color);
		}

		.view-item a,
		.sort-item-order a,
		.current {
			line-height: 25px;
		}

		.current-sort,
		.current-view {
			color: var(--dropdown-item-active-color);
			cursor: default;
		}

		.selected {
			position: relative;
			display: inline-block;
			width: 13px;
			height: 13px;
			border: 1px solid;
			margin-right: 5px;
		}

		.selected::after {
			content: "";
			position: absolute;
			left: -2px;
			top: -10px;
			width: 13px;
			height: 13px;
			border: solid var(--selected-checkbox-color);
			border-width: 0 2px 2px 0;
			transform: rotate(45deg);
		}

		/* --- Table View--- */
		table {
			margin: auto;
			border-collapse: collapse;
			border-spacing: 0;
		}

		thead tr {
			background-color: var(--thead-background);
			height: 50px;
			font-size: 25px;
		}

		.name-header {
			text-align: left;
		}

		tbody {
			display: block;
			max-height: calc(100vh - 195px);
			overflow-y: auto;
		}

		thead,
		tbody tr {
			display: table;
			width: 100%;
			table-layout: fixed; /* вирівнює колонки */
		}

		tbody tr:hover {
			background-color: var(--hover-color);
		}

		tr.row-odd {
			background-color: var(--row-background-odd);
		}

		tr.row-even {
			background-color: var(--row-background-even);
		}

		th:not(:first-child),
		td:not(:first-child) {
			text-align: center;
		}

		td,
		th {
			padding: 5px 10px;
		}

		th.size-header,
		td.size {
			text-align: end;
			width: 150px;
		}

		th.modified-header,
		td.modified,
		th.created-header,
		td.created,
		.action,
		.action-header {
			width: 200px;
		}

		.name-header,
		.name {
			width: auto;
			padding-left: 50px;
		}

		.name a {
			display: flex;
			align-items: center;
			column-gap: 10px;
			overflow-wrap: anywhere;
		}

		td.name svg {
			flex-shrink: 0;
		}

		td.name .file-icon-svg {
			height: 22px;
		}

		.action.download-btn {
			padding: 0 10px;
		}

		.download-btn > .fe-btn {
			display: inline-block;
			padding: 4px 12px;
		}

		.dir-icon-svg,
		.file-icon-svg {
			width: 25px;
			height: 25px;
		}

		.dir-icon-svg {
			margin-top: -3px;
		}

		/* --- Pagination --- */
		.fe-pagination {
			height: 75px;
			padding-top: 10px;
			display: flex;
			justify-content: center;
			align-items: flex-start;
			gap: 5px;
			flex-wrap: wrap;
		}

		.page-link {
			display: inline-flex;
			align-items: center;
			justify-content: center;
			min-width: 32px;
			height: 32px;
			padding: 0 8px;
			background: var(--btn-bg);
			border: 1px solid var(--border-color);
			color: var(--text-main);
			border-radius: 6px;
			font-size: 13px;
			text-decoration: none;
			transition: all 0.3s ease;
			user-select: none;
		}

		.page-link:hover {
			background: var(--btn-hover);
			border-color: var(--btn-shadow-color);
			box-shadow: 0 0 5px var(--btn-shadow-color), 0 0 15px var(--btn-shadow-color);
		}

		.page-link.active {
			background: var(--text-main);
			color: var(--bg-panel);
			border-color: var(--text-main);
			font-weight: bold;
			pointer-events: none;
		}

		.page-link.disabled {
			opacity: 0.4;
			pointer-events: none;
			cursor: default;
		}

		.page-dots {
			color: var(--text-secondary);
			padding: 0 4px;
		}

		@media screen and (max-width: 768px) {
			table {
				width: calc(100vw - 5px);
			}

			.created,
			.created-header {
				display: none;
			}

			.fe-header {
				flex-wrap: wrap;
				padding: 10px;
			}

			.fe-breadcrumbs {
				flex-basis: 100%;
				order: 5;
			}

			.fe-content::-webkit-scrollbar {
				width: 5px;
			}
		}
	</style>
	<script>
		/* --- Theme Logic --- */
		// Щоб не проморгувала світла тема при завантаженні сторінки в чорній темі
		const currentTheme = localStorage.getItem('theme');

		if (currentTheme) {
			document.documentElement.setAttribute('data-theme', currentTheme);
		}
	</script>
</head>
<body>
<div class="file-explorer-container">
	<!-- 1. HEADER -->
	<div class="fe-header">
		<!-- Buttons -->
		<button class="fe-btn" title="Home"><a href="<?php echo $baseUrl; ?>">Home</a></button>
		<button class="fe-btn back" title="Back">
			<a href="<?php echo $backUrl; ?>">
				<svg height="20px" width="40px" version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
					 viewBox="0 0 26.676 26.676" xml:space="preserve">
					<g>
						<path d="M26.105,21.891c-0.229,0-0.439-0.131-0.529-0.346l0,0c-0.066-0.156-1.716-3.857-7.885-4.59
							c-1.285-0.156-2.824-0.236-4.693-0.25v4.613c0,0.213-0.115,0.406-0.304,0.508c-0.188,0.098-0.413,0.084-0.588-0.033L0.254,13.815
							C0.094,13.708,0,13.528,0,13.339c0-0.191,0.094-0.365,0.254-0.477l11.857-7.979c0.175-0.121,0.398-0.129,0.588-0.029
							c0.19,0.102,0.303,0.295,0.303,0.502v4.293c2.578,0.336,13.674,2.33,13.674,11.674c0,0.271-0.191,0.508-0.459,0.562
							C26.18,21.891,26.141,21.891,26.105,21.891z"/>
					</g>
				</svg>
			</a>
		</button>
		<!-- Breadcrumbs -->
		<div class="fe-breadcrumbs">
			<span>/</span>
			<?php foreach ($breadcrumbs as $breadcrumb):
				if ($breadcrumb['isActive']):?>
					<a href="<?php echo $breadcrumb['url']; ?>"><?php echo htmlspecialchars($breadcrumb['name']); ?></a>
				<?php else: ?>
					<span class="current-dir"><?php echo htmlspecialchars($breadcrumb['name']); ?></span>
				<?php endif; ?>
				<span>/</span>
			<?php endforeach;?>
		</div>
		<!-- Sorting -->
		<div class="dropdown sort" data-dropdown>
			<button class="fe-btn view-toggle-btn" data-dropdown-button>Sort &#9662;</button>
			<div class="dropdown-content" data-dropdown-content>
				<?php foreach ($sortList as $sort => $orderList): ?>
					<div class="sort-item">
						<p><?php echo $sort; ?></p>
						<div class="sort-item-order">
							<?php foreach ($orderList as $order): ?>
								<?php if ($order['isActive']): ?>
									<a href="<?php echo $order['url']; ?>"><?php echo htmlspecialchars($order['name']); ?></a>
								<?php else: ?>
									<div class="current current-sort">
										<span class="selected"></span>
										<span ><?php echo htmlspecialchars($order['name']); ?></span>
									</div>
								<?php endif; ?>
							<?php endforeach; ?>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
		<!-- Filtering -->
		<div class="dropdown view" data-dropdown>
			<button class="fe-btn view-toggle-btn" data-dropdown-button>View &#9662;</button>
			<div class="dropdown-content" data-dropdown-content>
				<?php foreach ($viewUrlList as $viewUrl): ?>
					<div class="view-item">
						<?php if ($viewUrl['isActive']): ?>
							<a href="<?php echo $viewUrl['url']; ?>"><?php echo htmlspecialchars($viewUrl['name']); ?></a>
						<?php else: ?>
							<div class="current current-view">
								<span class="selected"></span>
								<span><?php echo htmlspecialchars($viewUrl['name']); ?></span>
							</div>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
		<div class="fe-search" data-fe-search>
			<form class="search-form" id="feSearchForm" onsubmit="return false">
				<input
						class="search-input"
						type="text"
						name="search"
						id="feSearchInput"
						placeholder="Search into current dir..."
						value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '';?>"
				/>
				<div class="search-clear-btn hidden" id="feSearchClear" title="Clean">
					<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 14 14">
						<path d="M7.89 7.032l5.92-5.945a.62.62 0 0 0 0-.895.613.613 0 0 0-.892 0L7 6.137 1.082.192a.613.613 0 0 0-.891 0 .62.62 0 0 0 0 .895l5.918 5.945-5.918 5.945a.62.62 0 0 0 0 .895C.318 14 .573 14 .7 14s.382 0 .382-.128L7 7.927l5.918 5.945c.127.128.382.128.51.128.126 0 .381 0 .381-.128a.62.62 0 0 0 0-.895L7.891 7.032z"/>
					</svg>
				</div>
				<button type="submit" class="search-submit" id="feSearchSubmit" title="Search">
					<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
						<circle cx="11" cy="11" r="8"></circle>
						<line x1="21" y1="21" x2="16.65" y2="16.65"></line>
					</svg>
				</button>
			</form>
		</div>
		<div class="theme-switcher-wrapper">
			<label class="theme-switcher" for="theme-switcher">
				<input type="checkbox" id="theme-switcher" data-theme-switcher/>
				<span class="slider round">
					<!-- Sun icon (Light theme) -->
					<svg class="icon-sun" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
					<!-- Moon icon (Dark theme) -->
					<svg class="icon-moon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>
				</span>
			</label>
		</div>
	</div>
	<!-- 2. Main content -->
	<div class="fe-content <?php echo ($template); ?>">
		<?php include 'template' . DIRECTORY_SEPARATOR . $template . '.php'; ?>
	</div>

	<?php if ($countPage > 1): ?>
		<?php
		$getPagingUrl = function ($page = null) {
			$params = $_GET;
			if ($page) {
				$params['page'] = $page;
			} else {
				unset($params['page']);
			}
			return '?' . http_build_query($params);
		};
		$delta = 1; // Number of pages to the left and right of the current page
		?>
		<div class="fe-pagination">
			<!-- Prev Button -->
			<a href="<?php echo $getPagingUrl(max(1, $currentPage - 1)); ?>"
			   class="page-link <?php echo ($currentPage <= 1) ? 'disabled' : ''; ?>">
				&lsaquo;
			</a>

			<a href="<?php echo $getPagingUrl(); ?>" class="page-link <?php echo ($currentPage === 1) ? 'active' : ''; ?>">1</a>

			<?php if ($currentPage > 2 + $delta): ?>
				<span class="page-dots">...</span>
			<?php endif; ?>

			<!-- Page Range -->
			<?php for ($i = max(2, $currentPage - $delta); $i <= min($countPage, $currentPage + $delta); $i++): ?>
				<a href="<?php echo $getPagingUrl($i); ?>"
				   class="page-link <?php echo ($i == $currentPage) ? 'active' : ''; ?>">
					<?php echo $i; ?>
				</a>
			<?php endfor; ?>

			<!-- Last Page -->
			<?php if ($currentPage < $countPage - $delta): ?>
				<span class="page-dots">...</span>
				<a href="<?php echo $getPagingUrl($countPage); ?>" class="page-link"><?php echo $countPage; ?></a>
			<?php endif; ?>

			<!-- Next Button -->
			<a href="<?php echo $getPagingUrl(min($countPage, $currentPage + 1)); ?>"
			   class="page-link <?php echo ($currentPage >= $countPage) ? 'disabled' : ''; ?>">
				&rsaquo;
			</a>
		</div>
	<?php endif; ?>
</div>
<script>
	/* --- Theme Logic too --- */
	const themeSwitcher = document.querySelector('[data-theme-switcher]');

	if (currentTheme === 'dark') {
		themeSwitcher.checked = true;
	}

	function switchTheme(e) {
		if (e.target.checked) {
			document.documentElement.setAttribute('data-theme', 'dark');
			localStorage.setItem('theme', 'dark');
		} else {
			document.documentElement.setAttribute('data-theme', 'light');
			localStorage.setItem('theme', 'light');
		}
	}

	themeSwitcher.addEventListener('change', switchTheme, false);

	/* --- Dropdown Logic --- */
	const dropdownList = document.querySelectorAll('[data-dropdown]'),
		dropdownButtonList = document.querySelectorAll('[data-dropdown-button]'),
		dropdownContentList = document.querySelectorAll('[data-dropdown-content]');

	dropdownButtonList.forEach(function (dropdownButton) {
		dropdownButton.addEventListener('click', function (e) {
			e.target.closest("[data-dropdown]").querySelector("[data-dropdown-content]").classList.toggle("show");
		});
	})

	dropdownList.forEach(function (dropdown) {
		dropdown.addEventListener('mouseleave', function (e) {
			e.target.querySelector("[data-dropdown-content]").classList.remove("show");
		});
	})

	dropdownContentList.forEach(function (dropdownContent) {
		dropdownContent.addEventListener('click', function (e) {
			e.target.closest("[data-dropdown-content]").classList.remove("show");
		});
	})

	document.addEventListener('keydown', function (e) {
		if (e.key === 'Backspace' && !e.target.closest('[data-fe-search]')) {
			window.location.href = '<?php echo $backUrl; ?>';
		}
	})

	/* --- Search Logic --- */
	const searchElement = document.querySelector('[data-fe-search]'),
		form = searchElement.querySelector('#feSearchForm'),
		input = searchElement.querySelector('#feSearchInput'),
		submitBtn = searchElement.querySelector('#feSearchSubmit'),
		clearBtn = searchElement.querySelector('#feSearchClear');

	function navigateWithSearch(value) {
		const url = new URL(window.location.href);
		if (value && value.trim() !== '') {
			url.searchParams.set('search', value.trim());
		} else {
			url.searchParams.delete('search');
		}

		// when new search happens, reset pagination if present
		url.searchParams.delete('page');
		window.location.href = url.toString();
	}

	form.addEventListener('submit', function(e){
		e.preventDefault();
		navigateWithSearch(input.value);
	});

	input.addEventListener('keydown', function(e){
		if (e.key === 'Enter') {
			e.preventDefault();
			navigateWithSearch(input.value);
		}
	});

	submitBtn.addEventListener('click', function(e){
		e.preventDefault();
		navigateWithSearch(input.value);
	});


	// Clear button visibility logic
	function updateClearVisibility() {
		const hasText = input && input.value.trim().length > 0;
		clearBtn.classList.toggle('hidden', !hasText);
	}

	updateClearVisibility();

	input.addEventListener('input', updateClearVisibility);

	clearBtn.addEventListener('click', function(){
		input.value = '';
		updateClearVisibility();
		input.focus();
	});
</script>
</body>
</html>
