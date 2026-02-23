// Test JavaScript Syntax
// Copy kode dari browser console dan paste di sini untuk test

// Simulasi data
const dosens = [{id: 1, nama: 'Dosen 1', nip: '123'}];
const mahasiswas = [{id: 1, nama: 'Mhs 1', npm: '456'}];

function escapeHtml(str) {
    return String(str ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

// Test closure
const testFunc = (function(cols, fieldKey) {
    return function() {
        console.log('Cols:', cols);
        console.log('FieldKey:', fieldKey);
        
        let cellsHtml = '';
        cols.forEach(col => {
            const colKey = col.key;
            const colLabel = col.label;
            const colType = col.type || 'text';
            const rowCount = 0;
            
            if (colType === 'pemohon') {
                const sources = ['mahasiswa','dosen'];
                
                const dosenOptions = dosens.map(d => '<option value="dosen:' + d.id + '">' + escapeHtml(d.nama) + ' (' + escapeHtml(d.nip) + ')</option>').join('');
                const mhsOptions = mahasiswas.map(m => '<option value="mahasiswa:' + m.id + '">' + escapeHtml(m.nama) + ' (' + escapeHtml(m.npm) + ')</option>').join('');
                
                let optionsHtml = '';
                if (sources.includes('mahasiswa')) {
                    optionsHtml += '<optgroup label="Mahasiswa">' + mhsOptions + '</optgroup>';
                }
                if (sources.includes('dosen')) {
                    optionsHtml += '<optgroup label="Dosen">' + dosenOptions + '</optgroup>';
                }
                
                cellsHtml += '<td class="px-4 py-2">' +
                    '<input type="hidden" class="pemohon-type" name="form_data[' + fieldKey + '][' + rowCount + '][' + colKey + '][type]" value="">' +
                    '<input type="hidden" class="pemohon-id" name="form_data[' + fieldKey + '][' + rowCount + '][' + colKey + '][id]" value="">' +
                    '<select class="pemohon-select w-full px-3 py-2 border border-gray-300 rounded-md text-sm">' +
                        '<option value="">Pilih ' + colLabel + '</option>' +
                        optionsHtml +
                    '</select>' +
                '</td>';
            } else {
                cellsHtml += '<td class="px-4 py-2">' +
                    '<input type="text" name="form_data[' + fieldKey + '][' + rowCount + '][' + colKey + ']" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm" placeholder="' + colLabel + '">' +
                '</td>';
            }
        });
        
        console.log('HTML:', cellsHtml);
    };
})([{key: 'nama', label: 'Nama', type: 'pemohon'}], 'test_field');

// Run test
testFunc();
console.log('Test completed successfully!');
