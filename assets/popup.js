jQuery(function ($) {
	/* ===============================
	 * CLEAN ?country= FROM URL (cross-domain redirect)
	 * =============================== */
	(function () {
		const url = new URL(window.location.href);
		if (url.searchParams.has('country')) {
			url.searchParams.delete('country');
			history.replaceState(null, '', url.pathname + url.search + url.hash);
		}
	})();

	/* ===============================
	 * CURRENCY SETTINGS (from admin)
	 * =============================== */
	const countryRules = CountryPopup.countryRules || {};
	const defaultCurrency = CountryPopup.defaultCurrency || 'USD';
	// Hàm lấy tiền tệ theo tên quốc gia (lookup ISO code → currency)
	function getCurrency(countryName) {
		const iso = countryToCode[countryName] || '';
		if (iso && countryRules[iso] && countryRules[iso].currency) {
			return countryRules[iso].currency;
		}
		return defaultCurrency;
	}
	/* ===============================
	 * BASE COUNTRY MAP (manually overridden)
	 * =============================== */
	const countryMap = {
		VN: 'Việt Nam',
		US: 'United States',
		JP: 'Japan',
		KR: 'South Korea',
		CN: 'China',
		FR: 'France',
		DE: 'Germany',
		GB: 'United Kingdom',
		AU: 'Australia',
	};
	/* ===============================
	 * ISO COUNTRY CODE MAP (195 COUNTRIES)
	 * =============================== */
	const countryToCode = {
		"Afghanistan": "AF","Albania": "AL","Algeria": "DZ","Andorra": "AD","Angola": "AO",
		"Antigua and Barbuda": "AG","Argentina": "AR","Armenia": "AM","Australia": "AU",
		"Austria": "AT","Azerbaijan": "AZ","Bahamas": "BS","Bahrain": "BH","Bangladesh": "BD",
		"Barbados": "BB","Belarus": "BY","Belgium": "BE","Belize": "BZ","Benin": "BJ",
		"Bhutan": "BT","Bolivia": "BO","Bosnia and Herzegovina": "BA","Botswana": "BW",
		"Brazil": "BR","Brunei": "BN","Bulgaria": "BG","Burkina Faso": "BF","Burundi": "BI",
		"Cabo Verde": "CV","Cambodia": "KH","Cameroon": "CM","Canada": "CA",
		"Central African Republic": "CF","Chad": "TD","Chile": "CL","China": "CN",
		"Colombia": "CO","Comoros": "KM","Congo": "CG",
		"Congo (Democratic Republic of the)": "CD","Costa Rica": "CR","Côte d’Ivoire": "CI",
		"Croatia": "HR","Cuba": "CU","Cyprus": "CY","Czech Republic": "CZ",
		"Denmark": "DK","Djibouti": "DJ","Dominica": "DM","Dominican Republic": "DO",
		"Ecuador": "EC","Egypt": "EG","El Salvador": "SV","Equatorial Guinea": "GQ",
		"Eritrea": "ER","Estonia": "EE","Eswatini": "SZ","Ethiopia": "ET","Fiji": "FJ",
		"Finland": "FI","France": "FR","Gabon": "GA","Gambia": "GM","Georgia": "GE",
		"Germany": "DE","Ghana": "GH","Greece": "GR","Grenada": "GD","Guatemala": "GT",
		"Guinea": "GN","Guinea-Bissau": "GW","Guyana": "GY","Haiti": "HT","Honduras": "HN",
		"Hong Kong": "HK","Hungary": "HU","Iceland": "IS","India": "IN","Indonesia": "ID",
		"Iran": "IR","Iraq": "IQ","Ireland": "IE","Israel": "IL","Italy": "IT",
		"Jamaica": "JM","Japan": "JP","Jordan": "JO","Kazakhstan": "KZ","Kenya": "KE",
		"Kiribati": "KI","Kuwait": "KW","Kyrgyzstan": "KG","Laos": "LA","Latvia": "LV",
		"Lebanon": "LB","Lesotho": "LS","Liberia": "LR","Libya": "LY","Liechtenstein": "LI",
		"Lithuania": "LT","Luxembourg": "LU","Madagascar": "MG","Malawi": "MW","Malaysia": "MY",
		"Maldives": "MV","Mali": "ML","Malta": "MT","Mauritania": "MR","Mauritius": "MU",
		"Mexico": "MX","Micronesia": "FM","Moldova": "MD","Monaco": "MC","Mongolia": "MN",
		"Montenegro": "ME","Morocco": "MA","Mozambique": "MZ","Myanmar": "MM","Namibia": "NA",
		"Nauru": "NR","Nepal": "NP","Netherlands": "NL","New Zealand": "NZ","Nicaragua": "NI",
		"Niger": "NE","Nigeria": "NG","North Korea": "KP","North Macedonia": "MK","Norway": "NO",
		"Oman": "OM","Pakistan": "PK","Palau": "PW","Panama": "PA","Papua New Guinea": "PG",
		"Paraguay": "PY","Peru": "PE","Philippines": "PH","Poland": "PL","Portugal": "PT",
		"Qatar": "QA","Romania": "RO","Russia": "RU","Rwanda": "RW",
		"Saint Kitts and Nevis": "KN","Saint Lucia": "LC","Saint Vincent and the Grenadines": "VC",
		"Samoa": "WS","San Marino": "SM","Saudi Arabia": "SA","Senegal": "SN","Serbia": "RS",
		"Seychelles": "SC","Sierra Leone": "SL","Singapore": "SG","Slovakia": "SK",
		"Slovenia": "SI","Solomon Islands": "SB","Somalia": "SO","South Africa": "ZA",
		"South Korea": "KR","South Sudan": "SS","Spain": "ES","Sri Lanka": "LK","Sudan": "SD",
		"Suriname": "SR","Sweden": "SE","Switzerland": "CH","Syria": "SY","Taiwan": "TW",
		"Tajikistan": "TJ","Tanzania": "TZ","Thailand": "TH","Timor-Leste": "TL",
		"Togo": "TG","Tonga": "TO","Trinidad and Tobago": "TT","Tunisia": "TN",
		"Turkey": "TR","Turkmenistan": "TM","Tuvalu": "TV","Uganda": "UG","Ukraine": "UA",
		"United Arab Emirates": "AE","United Kingdom": "GB","United States": "US",
		"Uruguay": "UY","Uzbekistan": "UZ","Vanuatu": "VU","Vatican City": "VA",
		"Venezuela": "VE","Việt Nam": "VN","Yemen": "YE","Zambia": "ZM","Zimbabwe": "ZW"
	};
	/* ===============================
	 * CONTINENTS
	 * =============================== */

	const continents = {
		Africa: [
			"Algeria","Angola","Benin","Botswana","Burkina Faso","Burundi",
			"Cabo Verde","Cameroon","Central African Republic","Chad","Comoros",
			"Congo","Congo (Democratic Republic of the)","Côte d’Ivoire",
			"Djibouti","Egypt","Equatorial Guinea","Eritrea","Eswatini",
			"Ethiopia","Gabon","Gambia","Ghana","Guinea","Guinea-Bissau",
			"Kenya","Lesotho","Liberia","Libya","Madagascar","Malawi","Mali",
			"Mauritania","Mauritius","Morocco","Mozambique","Namibia","Niger",
			"Nigeria","Rwanda","São Tomé and Príncipe","Senegal","Seychelles",
			"Sierra Leone","Somalia","South Africa","South Sudan","Sudan",
			"Tanzania","Togo","Tunisia","Uganda","Zambia","Zimbabwe"
		],
		Americas: [
			"Antigua and Barbuda","Argentina","Bahamas","Barbados","Belize","Bolivia",
			"Brazil","Canada","Chile","Colombia","Costa Rica","Cuba","Dominica",
			"Dominican Republic","Ecuador","El Salvador","Grenada","Guatemala",
			"Guyana","Haiti","Honduras","Jamaica","Mexico","Nicaragua","Panama",
			"Paraguay","Peru","Saint Kitts and Nevis","Saint Lucia",
			"Saint Vincent and the Grenadines","Suriname","Trinidad and Tobago",
			"United States","Uruguay","Venezuela"
		],
		Asia: [
			"Afghanistan","Armenia","Azerbaijan","Bahrain","Bangladesh","Bhutan",
			"Brunei","Cambodia","China","Cyprus","Georgia","Hong Kong","India",
			"Indonesia","Iran","Iraq","Israel","Japan","Jordan","Kazakhstan",
			"Kuwait","Kyrgyzstan","Laos","Lebanon","Macau","Malaysia","Maldives",
			"Mongolia","Myanmar","Nepal","North Korea","Oman","Pakistan",
			"Philippines","Qatar","Saudi Arabia","Singapore","South Korea",
			"Sri Lanka","Syria","Taiwan","Tajikistan","Thailand","Timor-Leste",
			"Turkey","Turkmenistan","United Arab Emirates","Uzbekistan","Việt Nam",
			"Yemen"
		],
		Europe: [
			"Albania","Andorra","Austria","Belarus","Belgium","Bosnia and Herzegovina",
			"Bulgaria","Croatia","Czech Republic","Denmark","Estonia","Finland",
			"France","Germany","Greece","Hungary","Iceland","Ireland","Italy",
			"Kosovo","Latvia","Liechtenstein","Lithuania","Luxembourg","Malta",
			"Moldova","Monaco","Montenegro","Netherlands","North Macedonia",
			"Norway","Poland","Portugal","Romania","Russia","San Marino","Serbia",
			"Slovakia","Slovenia","Spain","Sweden","Switzerland","Ukraine",
			"United Kingdom","Vatican City"
		],
		Oceania: [
			"Australia","Fiji","Kiribati","Marshall Islands","Micronesia",
			"Nauru","New Zealand","Palau","Papua New Guinea","Samoa",
			"Solomon Islands","Tonga","Tuvalu","Vanuatu"
		]
	};

	/* ===============================
	 * AUTO-MERGE CONTINENTS → countryMap
	 * =============================== */

	for (const continent in continents) {
		continents[continent].forEach(name => {
			const code = countryToCode[name];
			if (code && !countryMap[code]) {
				countryMap[code] = name;
			}
		});
	}
	/* ===============================
	 * STATE
	 * =============================== */
	const selectedISO = CountryPopup.confirmed || CountryPopup.geo || '';
	let selectedCountry = selectedISO;
	let selectorOpen = false;
	/* ===============================
	 * INIT
	 * =============================== */
	if (selectedCountry && countryMap[selectedCountry]) {
		const name = countryMap[selectedCountry];
		const currency = getCurrency(name);
		$('#selected-country-text').text(`${name} / ${currency}`);
	} else {
		$('#selected-country-text').text('—');
	}
	// 	$('#country-search, #continent-list').hide();
	render('');

	/* ===============================
	 * GEO PHÍA TRÌNH DUYỆT
	 * CountryPopup.geo được tính lúc render HTML (CF-IPCountry). Khi trang lấy
	 * từ page cache, đó là nước của người truy cập đầu tiên chứ không phải
	 * người đang xem -> lấy lại nước thật từ Cloudflare (/cdn-cgi/trace).
	 * Chỉ áp dụng khi chưa xác nhận quốc gia (không có cookie).
	 * =============================== */
	if (!CountryPopup.confirmed) {
		fetch('/cdn-cgi/trace', { credentials: 'omit', cache: 'no-store' })
			.then(r => r.ok ? r.text() : '')
			.then(txt => {
				const m = /^loc=([A-Z]{2})$/m.exec(txt || '');
				const loc = m ? m[1] : '';
				if (!loc || loc === 'XX' || !countryMap[loc]) return;
				const currency = (countryRules[loc] && countryRules[loc].currency) || defaultCurrency;
				// Nhãn trên header/footer ("VN / VND")
				$('.ms-country-switcher').text(`${loc} / ${currency}`);
				// Popup: chỉ đổi nếu khách chưa tự chọn nước khác
				if (selectedCountry === selectedISO) {
					selectedCountry = loc;
					$('#selected-country-text').text(`${countryMap[loc]} / ${getCurrency(countryMap[loc])}`);
				}
			})
			.catch(() => {});
	}
	/* ===============================
	 * TOGGLE SELECTOR
	 * =============================== */
	
	
	$('#toggle-selector').on('click', function () {
		selectorOpen = !selectorOpen;
		$('.select-country, .select-country-title').slideToggle(200);
		$('#toggle-selector').toggleClass('active');
// 		$(this).text(selectorOpen ? '▴' : '▾');
	});
	
	
	
	/* ===============================
	 * SEARCH
	 * =============================== */
	$('#country-search').on('input', function () {
		render($(this).val());
	});
	/* ===============================
	 * SELECT COUNTRY
	 * =============================== */
	$(document).on('click', '.country', function () {
		selectedCountry = $(this).data('code');
		const name = $(this).contents().first().text().trim();
		const currency = getCurrency(name);
		// Cập nhật text hiển thị trên nút
		$('#selected-country-text').text(`${name} / ${currency}`);
		// Xóa active toàn bộ, thêm active vào cái được chọn
		$('.country').removeClass('active');
		$(this).addClass('active');
		$('.select-country, .select-country-title').slideToggle(200);
	});
	/* ===============================
	 * CONFIRM
	 * =============================== */

	$('#confirm-country').on('click', function () {
		if (!selectedCountry) return;
		const $btn = $(this);
		$btn.prop('disabled', true).text('Saving...');
		$.post(CountryPopup.ajax, {
			action: 'ms_save_country',
			country: selectedCountry
		}).done(res => {
			if (!res.success) {
				alert('Save failed');
				$btn.prop('disabled', false).text('XÁC NHẬN');
				return;
			}
			const redirectUrl = res.data && res.data.redirect_url;
			if (redirectUrl) {
				// Kiểm tra redirect có khác domain hiện tại không
				try {
					const target = new URL(redirectUrl);
					if (target.hostname !== window.location.hostname) {
						window.location.href = redirectUrl;
						return;
					}
				} catch (e) { /* invalid URL, fallback to reload */ }
			}
			location.reload();
		}).fail(() => {
			alert('Server error');
			$btn.prop('disabled', false).text('XÁC NHẬN');
		});
	});

	/* ===============================
	 * RENDER CONTINENTS
	 * =============================== */
	function render(keyword = '') {
		let html = '';
		keyword = keyword.toLowerCase().trim();
		let autoOpened = false;
		$.each(continents, function (continent, countries) {
			const matches = countries.filter(c =>
											 c.toLowerCase().includes(keyword)
											).sort();
			if (!matches.length) return;
			let autoOpen = keyword && !autoOpened && matches.length <= 2;
			if (autoOpen) autoOpened = true;
			html += `
<div class="continent ${autoOpen ? 'open' : ''}">
<div class="continent-title">
${continent}
<span class="toggle">${autoOpen ? '<svg xmlns="http://www.w3.org/2000/svg" id="minus" viewBox="0 0 17 17"><path fill-rule="evenodd" d="M4.14999 8.15039H12.35v1.2H4.14999v-1.2Z"/></svg>' : '<svg xmlns="http://www.w3.org/2000/svg" id="plus-small" viewBox="0 0 17 17"><path fill-rule="evenodd" d="M9.25 5.25v7h-1v-7h1Z"/><path fill-rule="evenodd" d="M5.24998 8.25H12.25v1H5.24998v-1Z"/></svg>'}</span>
</div>
<div class="country-list" style="display:${autoOpen ? 'block' : 'none'}">
`;
			matches.forEach(name => {
				const iso = countryToCode[name] || '';
				const currency = getCurrency(name);
				html += `
<div class="country" data-code="${iso}">
${name} <span class="currency-label">${currency}</span>
</div>`;
			});
			html += `</div></div>`;
		});

		$('#continent-list').html(html);
	}
	/* ===============================
	 * ACCORDION (1 OPEN ONLY)
	 * =============================== */
	$(document).on('click', '.continent-title', function () {
		const $list = $(this).next('.country-list');
		const isOpen = $list.is(':visible');
		$('.country-list:visible').slideUp(150);
		$('.continent-title .toggle').html('<svg xmlns="http://www.w3.org/2000/svg" id="plus-small" viewBox="0 0 17 17"><path fill-rule="evenodd" d="M9.25 5.25v7h-1v-7h1Z"/><path fill-rule="evenodd" d="M5.24998 8.25H12.25v1H5.24998v-1Z"/></svg>');
		if (!isOpen) {
			$list.slideDown(150);
			$(this).find('.toggle').html('<svg xmlns="http://www.w3.org/2000/svg" id="minus" viewBox="0 0 17 17"><path fill-rule="evenodd" d="M4.14999 8.15039H12.35v1.2H4.14999v-1.2Z"/></svg>');
		}
	});
	/* ===============================
	 * FLATSOME LIGHTBOX OPEN
	 * =============================== */
	$(document).on('mfpOpen', function () {
		$('#country-search, #continent-list').show();
		render('');
	});

	/* ===============================
	 * FIX BROWSER BACK BUTTON (BFCACHE)
	 * =============================== */
	$(window).on('pageshow', function (event) {
		if (event.originalEvent && event.originalEvent.persisted) {
			window.location.reload();
		}
	});

});
