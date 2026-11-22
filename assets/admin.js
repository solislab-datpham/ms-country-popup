jQuery(function ($) {
	'use strict';

	/* ===============================
	 * NEXT INDEX HELPER
	 * =============================== */
	function getNextIndex(tableId) {
		let max = -1;
		$('#' + tableId + ' tbody input').each(function () {
			const match = this.name.match(/\[(\d+)\]/);
			if (match) {
				const idx = parseInt(match[1], 10);
				if (idx > max) max = idx;
			}
		});
		return max + 1;
	}

	/* ===============================
	 * ROW TEMPLATE BUILDERS
	 * =============================== */
	const builders = {
		'rules-table': function (idx) {
			return `<tr>
			<td><input type="text" name="ms_cp_country_rules[${idx}][code]" value="" placeholder="VN" maxlength="2"></td>
			<td><input type="text" name="ms_cp_country_rules[${idx}][domain]" value="" placeholder="https://cenes.vn" class="regular-text"></td>
			<td><input type="text" name="ms_cp_country_rules[${idx}][currency]" value="" placeholder="VND" maxlength="5"></td>
			<td><button type="button" class="button ms-cp-remove-row">✕</button></td>
		</tr>`;
		}
	};

	/* ===============================
	 * ADD ROW
	 * =============================== */
	$('.ms-cp-add-row').on('click', function () {
		const tableId = $(this).data('table');
		const builder = builders[tableId];
		if (builder) {
			const idx = getNextIndex(tableId);
			$('#' + tableId + ' tbody').append(builder(idx));
		}
	});

	/* ===============================
	 * REMOVE ROW
	 * =============================== */
	$(document).on('click', '.ms-cp-remove-row', function () {
		$(this).closest('tr').remove();
	});

	/* ===============================
	 * AUTO UPPERCASE COUNTRY CODE
	 * =============================== */
	$(document).on('input', '.ms-cp-table input[maxlength="2"]', function () {
		this.value = this.value.toUpperCase();
	});
	$(document).on('input', '.ms-cp-table input[maxlength="5"]', function () {
		this.value = this.value.toUpperCase();
	});
});
