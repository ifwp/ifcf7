var ifcf7_admin_table = {

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	load: function(){
		jQuery(function($){
			$('.ifcf7-admin-table').each(function(index, element){
				// Setup - add a text input to each footer cell
				$(element).children('thead').children('tr').clone(true).addClass('filters').appendTo($(element).children('thead'));
			});
			$('.ifcf7-admin-table').DataTable({
				autoWidth: false,
				buttons: [{
					extend: 'copyHtml5',
					text: function(dt, button, config){
						return '<i class="fas fa-copy mr-2"></i>' + dt.i18n('buttons.copy', 'Copy');
					},
				}, {
					extend: 'csvHtml5',
					text: function(dt, button, config){
						return '<i class="fas fa-file-csv mr-2"></i>' + dt.i18n('buttons.csv', 'CSV');
					},
				}, {
					autoFilter: true,
					extend: 'excelHtml5',
					text: function(dt, button, config){
						return '<i class="fas fa-file-excel mr-2"></i>' + dt.i18n('buttons.excel', 'Excel');
					},
				}, {
					text: '<i class="fas fa-file-code mr-2"></i>JSON',
					action: function(e, dt, button, config){
						var data = dt.buttons.exportData();
						$.fn.dataTable.fileSave(new Blob([JSON.stringify(data)]), 'Export.json');
					}
				}, {
					extend: 'pdfHtml5',
					orientation: 'landscape',
					text: function(dt, button, config){
						return '<i class="fas fa-file-pdf mr-2"></i>' + dt.i18n('buttons.pdf', 'PDF');
					},
				}, {
					extend: 'print',
					text: function(dt, button, config){
						return '<i class="fas fa-print mr-2"></i>' + dt.i18n('buttons.print', 'Print');
					},
				}, {
					extend: 'colvis',
					text: function(dt, button, config){
						return '<i class="fas fa-columns mr-2"></i>' + dt.i18n('buttons.colvis', 'Column visibility');
					},
				}],
				dom: '<"clearfix border-bottom mb-3 pb-3 text-center"B><"row"<"col-md-6"l><"col-md-6"f>><"mb-3"t><"row"<"col-md-6"i><"col-md-6"p>>',
				//fixedHeader: true,
				initComplete: function(){
					var api = this.api();
					// For each column
					api.columns().eq(0).each(function(colIdx){
						// Set the header cell to contain the input element
						var cell = $('.filters th').eq($(api.column(colIdx).header()).index());
						var title = $(cell).text();
						$(cell).html('<input class="form-control form-control-sm" type="text" placeholder="' + title + '" />');
						// On every keypress in this input
						$('input', $('.filters th').eq($(api.column(colIdx).header()).index())).off('keyup change').on('keyup change', function(e){
							e.stopPropagation();
							// Get the search value
							$(this).attr('title', $(this).val());
							var regexr = '({search})'; //$(this).parents('th').find('select').val();
							var cursorPosition = this.selectionStart;
							// Search the column for that value
							api.column(colIdx).search(this.value != '' ? regexr.replace('{search}', '(((' + this.value + ')))') : '', this.value != '', this.value == '').draw();
							$(this).focus()[0].setSelectionRange(cursorPosition, cursorPosition);
						});
					});
				},
				language: {
					url: 'https://cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json',
				},
				lengthMenu: [[20, 50, 100, -1], [20, 50, 100, 'All']],
				orderCellsTop: true,
				pageLength: 20,
				scrollX: true,
			});
		});
	},

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

};
