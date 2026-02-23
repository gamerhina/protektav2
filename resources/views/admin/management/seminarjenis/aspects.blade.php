@extends('layouts.app')

@section('title', 'Kelola Aspek Penilaian')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
        <div class="mb-6">
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-800">Aspek Penilaian</h1>
                    <p class="text-gray-600 mt-1">{{ $seminarJenis->nama }} ({{ $seminarJenis->kode }})</p>
                </div>
                <a href="{{ route('admin.seminarjenis.index') }}" class="btn-pill btn-pill-secondary justify-center sm:justify-start">
                    Kembali
                </a>
            </div>
        </div>

        <div class="mb-8 bg-blue-50 border border-blue-200 rounded-lg p-4">
            <h3 class="font-medium text-blue-800 mb-3">Tambah Aspek Penilaian Baru</h3>
            <form action="{{ route('admin.seminarjenis.aspects.store', $seminarJenis) }}" method="POST" class="grid grid-cols-1 md:grid-cols-6 gap-4">
                @csrf
                <div>
                    <label for="evaluator_type_add" class="block text-sm font-medium text-gray-700 mb-1">Penilai</label>
                    <select name="evaluator_type" id="evaluator_type_add" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm" required>
                        <option value="">Pilih Penilai</option>
                        <option value="p1">Pembimbing 1 (P1)</option>
                        <option value="p2">Pembimbing 2 (P2)</option>
                        <option value="pembahas">Pembahas (PMB)</option>
                    </select>
                </div>
                <div>
                    <label for="type_add" class="block text-sm font-medium text-gray-700 mb-1">Jenis</label>
                    <select name="type" id="type_add" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm" required>
                        <option value="input">Input Angka</option>
                        <option value="sum">Jumlah (Sum)</option>
                        <option value="prev_avg">Rata-rata (Avg)</option>
                    </select>
                </div>
                <div>
                    <label for="category_add" class="block text-sm font-medium text-gray-700 mb-1">Kat.</label>
                    <input type="text" name="category" id="category_add" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm" placeholder="Grup A">
                </div>
                <div class="md:col-span-2">
                    <label for="nama_aspek_add" class="block text-sm font-medium text-gray-700 mb-1">Nama Aspek</label>
                    <input type="text" name="nama_aspek" id="nama_aspek_add" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm" required>
                </div>
                
                <div>
                    <label for="urutan_add" class="block text-sm font-medium text-gray-700 mb-1">Urutan</label>
                    <input type="number" name="urutan" id="urutan_add" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm" required>
                </div>

                <div id="related_aspects_container_add" class="md:col-span-6 hidden bg-gray-50 border border-blue-200 rounded-lg p-4 mt-2">
                    <label class="block text-sm font-bold text-blue-800 mb-2">Pilih Aspek Komponen (Input):</label>
                    <div id="aspect_list_add" class="grid grid-cols-1 md:grid-cols-3 gap-2"></div>
                </div>

                <div class="md:col-span-6 flex justify-end">
                    <button type="submit" class="btn-pill btn-pill-primary px-8">
                        Tambah Aspek
                    </button>
                </div>
            </form>
        </div>

        @foreach(['p1' => 'Pembimbing 1 (P1)', 'p2' => 'Pembimbing 2 (P2)', 'pembahas' => 'Pembahas (PMB)'] as $type => $label)
            <div class="mb-8">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-semibold text-gray-800">{{ $label }}</h2>
                    @if(isset($aspects[$type]) && $aspects[$type]->count() > 0)
                        <span class="px-3 py-1 rounded-md text-sm font-bold bg-blue-100 text-blue-800">
                             {{ $aspects[$type]->count() }} Aspek
                        </span>
                    @endif
                </div>
                
                @if(isset($aspects[$type]) && $aspects[$type]->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 border">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left text-[10px] font-bold text-gray-500 uppercase">Seq</th>
                                    <th class="px-4 py-2 text-left text-[10px] font-bold text-gray-500 uppercase">Jenis</th>
                                    <th class="px-4 py-2 text-left text-[10px] font-bold text-gray-500 uppercase">Kat.</th>
                                    <th class="px-4 py-2 text-left text-[10px] font-bold text-gray-500 uppercase">Nama Aspek</th>
                                    <th class="px-4 py-2 text-left text-[10px] font-bold text-gray-500 uppercase">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($aspects[$type] as $aspect)
                                <tr>
                                    <td class="px-4 py-3">{{ $aspect->urutan }}</td>
                                    <td class="px-4 py-3 text-[10px] font-bold uppercase">
                                         @if($aspect->type === 'input')
                                            <span class="text-blue-600">INPUT</span>
                                        @elseif($aspect->type === 'sum')
                                            <span class="text-emerald-600">SUM</span>
                                        @else
                                            <span class="text-purple-600">AVG</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-[10px] text-gray-500">{{ $aspect->category ?: '-' }}</td>
                                    <td class="px-4 py-3 font-medium">{{ $aspect->nama_aspek }}</td>
                                    <td class="px-4 py-3 text-sm">
                                        <div class="flex space-x-2">
                                            <button onclick="editAspect({{ json_encode($aspect) }})" class="text-blue-600 hover:text-blue-900 font-semibold" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <form action="{{ route('admin.seminarjenis.aspects.destroy', [$seminarJenis, $aspect]) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus aspek ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-500 hover:text-red-700 font-semibold" title="Hapus">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="bg-gray-50 rounded-lg p-6 text-center">
                        <p class="text-gray-500">Belum ada aspek penilaian untuk {{ $label }}</p>
                    </div>
                @endif
            </div>
        @endforeach
    </div>
</div>

<script id="aspects-data" type="application/json">
    {!! json_encode($seminarJenis->assessmentAspects()->get()->groupBy('evaluator_type')) !!}
</script>

<div id="editModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-[600px] shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4 border-b pb-2">Ubah Aspek Penilaian</h3>
            <form id="editForm" method="POST">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-2 gap-4">
                    <div class="mb-4">
                        <label for="edit_type" class="block text-sm font-medium text-gray-700 mb-1">Jenis</label>
                        <select name="type" id="edit_type" class="w-full px-3 py-2 border border-gray-300 rounded-md" required>
                            <option value="input">Input Angka</option>
                            <option value="sum">Jumlah (Sum)</option>
                            <option value="prev_avg">Rata-rata (Avg)</option>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label for="edit_category" class="block text-sm font-medium text-gray-700 mb-1">Kategori</label>
                        <input type="text" name="category" id="edit_category" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                    </div>
                </div>
                <div class="mb-4">
                    <label for="edit_nama_aspek" class="block text-sm font-medium text-gray-700 mb-1">Nama Aspek</label>
                    <input type="text" name="nama_aspek" id="edit_nama_aspek" class="w-full px-3 py-2 border border-gray-300 rounded-md" required>
                </div>
                <div class="mb-4">
                     <label for="edit_urutan" class="block text-sm font-medium text-gray-700 mb-1">Urutan</label>
                     <input type="number" name="urutan" id="edit_urutan" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-md" required>
                </div>

                <div id="related_aspects_container_edit" class="hidden bg-gray-50 border border-blue-200 rounded-lg p-4 mt-2 mb-4">
                    <label class="block text-sm font-bold text-blue-800 mb-2 uppercase text-[10px]">Pilih Aspek Komponen (Input):</label>
                    <div id="aspect_list_edit" class="grid grid-cols-1 md:grid-cols-2 gap-2"></div>
                </div>

                <div class="flex justify-end space-x-2 pt-4 border-t">
                    <button type="button" onclick="closeEditModal()" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">
                        Kembali
                    </button>
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@section('scripts')
<script>
(function() {
    const allAspects = JSON.parse(document.getElementById('aspects-data').textContent || '{}');

    const updateUI = (containerId, typeValue) => {
        // No UI percentages to toggle
    };

    const updateAspectList = (evaluatorType, containerId, listId, selectedIds = []) => {
        const container = document.getElementById(containerId);
        const list = document.getElementById(listId);
        const typeSelect = containerId.includes('add') ? document.getElementById('type_add') : document.getElementById('edit_type');
        
        if (!evaluatorType || (typeSelect.value === 'input')) {
            container.classList.add('hidden');
            updateUI(containerId, 'input');
            return;
        }
        
        updateUI(containerId, typeSelect.value);

        container.classList.remove('hidden');
        let data = allAspects[evaluatorType] || [];
        
        if (containerId.includes('edit') && window._editingAspect) {
            data = data.filter(a => a.id !== window._editingAspect.id);
        }

        list.innerHTML = '';
        
        if (data.length === 0) {
            list.innerHTML = '<p class="text-xs text-gray-500 italic md:col-span-3">Belum ada aspek input lainnya.</p>';
        } else {
             data.forEach(aspect => {
                const isChecked = selectedIds.includes(aspect.id) || selectedIds.includes(String(aspect.id));
                const isInput = aspect.type === 'input';
                const badge = isInput 
                    ? '<span class="text-[10px] bg-blue-100 text-blue-800 px-1.5 py-0.5 rounded font-medium">Input</span>' 
                    : '<span class="text-[10px] bg-purple-100 text-purple-800 px-1.5 py-0.5 rounded font-medium">' + (aspect.type === 'sum' ? 'Sum' : 'Avg') + '</span>';

                const div = document.createElement('div');
                div.className = 'flex items-center space-x-2 p-2 hover:bg-gray-50 rounded transition-colors border border-transparent hover:border-gray-200 group';
                div.innerHTML = `
                    <input type="checkbox" name="related_aspects[]" value="${aspect.id}" id="rel_${containerId}_${aspect.id}" ${isChecked ? 'checked' : ''} class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 cursor-pointer">
                    <label for="rel_${containerId}_${aspect.id}" class="text-sm text-gray-700 cursor-pointer flex-1 flex items-center justify-between ml-2">
                        <span class="truncate mr-2 font-medium" title="${aspect.nama_aspek}">${aspect.nama_aspek}</span>
                        ${badge}
                    </label>
                `;
                list.appendChild(div);
            });
        }
    };

    window.editAspect = function(aspect) {
        window._editingAspect = aspect;
        document.getElementById('edit_nama_aspek').value = aspect.nama_aspek;
        // document.getElementById('edit_persentase').value = aspect.persentase; 
        document.getElementById('edit_urutan').value = aspect.urutan;
        document.getElementById('edit_type').value = aspect.type;
        document.getElementById('edit_category').value = aspect.category || '';
        
        // Dynamic List
        const typeSelect = document.getElementById('edit_type');
        updateUI('related_aspects_container_edit', aspect.type);
        
        if (aspect.type !== 'input') {
             document.getElementById('related_aspects_container_edit').classList.remove('hidden');
             updateAspectList(aspect.evaluator_type, 'related_aspects_container_edit', 'aspect_list_edit', aspect.related_aspects || []);
        } else {
             document.getElementById('related_aspects_container_edit').classList.add('hidden');
        }
        
        document.getElementById('editForm').action = '{{ route('admin.seminarjenis.aspects.update', [$seminarJenis, '__ID__']) }}'.replace('__ID__', aspect.id);
        document.getElementById('editModal').classList.remove('hidden');
    }

    const typeAdd = document.getElementById('type_add');
    const evalAdd = document.getElementById('evaluator_type_add');
    if (typeAdd && evalAdd) {
        const up = () => updateAspectList(evalAdd.value, 'related_aspects_container_add', 'aspect_list_add');
        typeAdd.addEventListener('change', up);
        evalAdd.addEventListener('change', up);
        // Initial init
        updateUI('related_aspects_container_add', typeAdd.value);
    }

    const typeEdit = document.getElementById('edit_type');
    if(typeEdit) {
        typeEdit.addEventListener('change', () => {
             if(window._editingAspect) updateAspectList(window._editingAspect.evaluator_type, 'related_aspects_container_edit', 'aspect_list_edit', window._editingAspect.related_aspects || []);
        });
    }

    window.closeEditModal = function() {
        document.getElementById('editModal').classList.add('hidden');
    }

    function initAspectsPage() {
        const modal = document.getElementById('editModal');
        if (!modal || modal.dataset.initialized === 'true') return;

        modal.addEventListener('click', function(e) {
            if (e.target === this) closeEditModal();
        });

        modal.dataset.initialized = 'true';
    }

    if (document.readyState !== 'loading') initAspectsPage();
    else document.addEventListener('DOMContentLoaded', initAspectsPage);
    window.addEventListener('page-loaded', initAspectsPage);
})();
</script>
@endsection
            <h3 class="font-medium text-blue-800 mb-3">Tambah Aspek Penilaian Baru</h3>
            <form action="{{ route('admin.seminarjenis.aspects.store', $seminarJenis) }}" method="POST" class="grid grid-cols-1 md:grid-cols-6 gap-4">
                @csrf
                <div>
                    <label for="evaluator_type_add" class="block text-sm font-medium text-gray-700 mb-1">Penilai</label>
                    <select name="evaluator_type" id="evaluator_type_add" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm" required>
                        <option value="">Pilih Penilai</option>
                        <option value="p1">Pembimbing 1 (P1)</option>
                        <option value="p2">Pembimbing 2 (P2)</option>
                        <option value="pembahas">Pembahas (PMB)</option>
                    </select>
                </div>
                <div>
                    <label for="type_add" class="block text-sm font-medium text-gray-700 mb-1">Jenis</label>
                    <select name="type" id="type_add" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm" required>
                        <option value="input">Input Angka</option>
                        <option value="sum">Jumlah (Sum)</option>
                        <option value="prev_avg">Rata-rata (Avg)</option>
                    </select>
                </div>
                <div>
                    <label for="category_add" class="block text-sm font-medium text-gray-700 mb-1">Kat.</label>
                    <input type="text" name="category" id="category_add" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm" placeholder="Grup A">
                </div>
                <div>
                    <label for="nama_aspek_add" class="block text-sm font-medium text-gray-700 mb-1">Nama Aspek</label>
                    <input type="text" name="nama_aspek" id="nama_aspek_add" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm" required>
                </div>
                <div>
                    <label for="persentase_add" class="block text-sm font-medium text-gray-700 mb-1">Bobot %</label>
                    <input type="number" name="persentase" id="persentase_add" min="0" max="100" step="0.01" value="0" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm" required>
                </div>
                <div>
                    <label for="urutan_add" class="block text-sm font-medium text-gray-700 mb-1">Urutan</label>
                    <input type="number" name="urutan" id="urutan_add" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm" required>
                </div>

                <div id="related_aspects_container_add" class="md:col-span-6 hidden bg-white border border-blue-200 rounded-lg p-4 mt-2">
                    <label class="block text-sm font-bold text-blue-800 mb-2">Pilih Aspek yang akan dihitung:</label>
                    <div id="aspect_list_add" class="grid grid-cols-1 md:grid-cols-3 gap-2"></div>
                </div>

                <div class="md:col-span-6 flex justify-end">
                    <button type="submit" class="btn-pill btn-pill-primary px-8">
                        Tambah Aspek
                    </button>
                </div>
            </form>
        </div>

        @foreach(['p1' => 'Pembimbing 1 (P1)', 'p2' => 'Pembimbing 2 (P2)', 'pembahas' => 'Pembahas (PMB)'] as $type => $label)
            <div class="mb-8">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-semibold text-gray-800">{{ $label }}</h2>
                    @if(isset($aspects[$type]) && $aspects[$type]->count() > 0)
                        @php $totalP = $aspects[$type]->sum('persentase'); @endphp
                        <span class="px-3 py-1 rounded-md text-sm font-bold {{ $totalP == 100 ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">
                            Total: {{ $totalP }}%
                        </span>
                    @endif
                </div>
                
                @if(isset($aspects[$type]) && $aspects[$type]->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 border">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left text-[10px] font-bold text-gray-500 uppercase">Seq</th>
                                    <th class="px-4 py-2 text-left text-[10px] font-bold text-gray-500 uppercase">Jenis</th>
                                    <th class="px-4 py-2 text-left text-[10px] font-bold text-gray-500 uppercase">Kat.</th>
                                    <th class="px-4 py-2 text-left text-[10px] font-bold text-gray-500 uppercase">Nama Aspek</th>
                                    <th class="px-4 py-2 text-left text-[10px] font-bold text-gray-500 uppercase">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($aspects[$type] as $aspect)
                                <tr>
                                    <td class="px-4 py-3">{{ $aspect->urutan }}</td>
                                    <td class="px-4 py-3 text-[10px] font-bold uppercase">{{ $aspect->type }}</td>
                                    <td class="px-4 py-3 text-[10px] text-gray-500">{{ $aspect->category ?: '-' }}</td>
                                    <td class="px-4 py-3 font-medium">{{ $aspect->nama_aspek }}</td>
                                    <td class="px-4 py-3 text-sm">
                                        <div class="flex space-x-2">
                                            <button onclick="editAspect({{ json_encode($aspect) }})" class="text-blue-600 hover:text-blue-900 font-semibold" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <form action="{{ route('admin.seminarjenis.aspects.destroy', [$seminarJenis, $aspect]) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus aspek ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-500 hover:text-red-700 font-semibold" title="Hapus">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="bg-gray-50 rounded-lg p-6 text-center">
                        <p class="text-gray-500">Belum ada aspek penilaian untuk {{ $label }}</p>
                    </div>
                @endif
            </div>
        @endforeach
    </div>
</div>

<script id="aspects-data" type="application/json">
    {!! json_encode($seminarJenis->assessmentAspects()->where('type', 'input')->get()->groupBy('evaluator_type')) !!}
</script>

<div id="editModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-[600px] shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4 border-b pb-2">Ubah Aspek Penilaian</h3>
            <form id="editForm" method="POST">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-2 gap-4">
                    <div class="mb-4">
                        <label for="edit_type" class="block text-sm font-medium text-gray-700 mb-1">Jenis</label>
                        <select name="type" id="edit_type" class="w-full px-3 py-2 border border-gray-300 rounded-md" required>
                            <option value="input">Input Angka</option>
                            <option value="sum">Jumlah (Sum)</option>
                            <option value="prev_avg">Rata-rata (Avg)</option>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label for="edit_category" class="block text-sm font-medium text-gray-700 mb-1">Kategori</label>
                        <input type="text" name="category" id="edit_category" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                    </div>
                </div>
                <div class="mb-4">
                    <label for="edit_nama_aspek" class="block text-sm font-medium text-gray-700 mb-1">Nama Aspek</label>
                    <input type="text" name="nama_aspek" id="edit_nama_aspek" class="w-full px-3 py-2 border border-gray-300 rounded-md" required>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="mb-4">
                        <label for="edit_persentase" class="block text-sm font-medium text-gray-700 mb-1">Bobot (%)</label>
                        <input type="number" name="persentase" id="edit_persentase" min="0" max="100" step="0.01" class="w-full px-3 py-2 border border-gray-300 rounded-md" required>
                    </div>
                    <div class="mb-4">
                        <label for="edit_urutan" class="block text-sm font-medium text-gray-700 mb-1">Urutan</label>
                        <input type="number" name="urutan" id="edit_urutan" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-md" required>
                    </div>
                </div>

                <div id="related_aspects_container_edit" class="hidden bg-white border border-blue-200 rounded-lg p-4 mt-2 mb-4">
                    <label class="block text-sm font-bold text-blue-800 mb-2 uppercase text-[10px]">Pilih Aspek Penilai:</label>
                    <div id="aspect_list_edit" class="grid grid-cols-1 md:grid-cols-2 gap-2"></div>
                </div>

                <div class="flex justify-end space-x-2 pt-4 border-t">
                    <button type="button" onclick="closeEditModal()" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">
                        Kembali
                    </button>
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@section('scripts')
<script>
(function() {
    const allAspects = JSON.parse(document.getElementById('aspects-data').textContent || '{}');

    const updateAspectList = (evaluatorType, containerId, listId, selectedIds = []) => {
        const container = document.getElementById(containerId);
        const list = document.getElementById(listId);
        const typeSelect = containerId.includes('add') ? document.getElementById('type_add') : document.getElementById('edit_type');
        
        if (!evaluatorType || typeSelect.value === 'input') {
            container.classList.add('hidden');
            return;
        }

        container.classList.remove('hidden');
        const data = allAspects[evaluatorType] || [];
        list.innerHTML = '';
        
        if (data.length === 0) {
            list.innerHTML = '<p class="text-xs text-gray-500 italic">Belum ada aspek lain.</p>';
        } else {
            data.forEach(aspect => {
                const isChecked = selectedIds.includes(aspect.id) || selectedIds.includes(String(aspect.id));
                const div = document.createElement('div');
                div.className = 'flex items-center space-x-2 p-1.5 hover:bg-gray-50 rounded';
                div.innerHTML = `
                    <input type="checkbox" name="related_aspects[]" value="${aspect.id}" id="rel_${containerId}_${aspect.id}" ${isChecked ? 'checked' : ''} class="rounded text-blue-600">
                    <label for="rel_${containerId}_${aspect.id}" class="text-xs text-gray-700 truncate">${aspect.nama_aspek}</label>
                `;
                list.appendChild(div);
            });
        }
    };

    window.editAspect = function(aspect) {
        window._editingAspect = aspect;
        document.getElementById('edit_nama_aspek').value = aspect.nama_aspek;
        document.getElementById('edit_persentase').value = aspect.persentase;
        document.getElementById('edit_urutan').value = aspect.urutan;
        document.getElementById('edit_type').value = aspect.type;
        document.getElementById('edit_category').value = aspect.category || '';
        
        updateAspectList(aspect.evaluator_type, 'related_aspects_container_edit', 'aspect_list_edit', aspect.related_aspects || []);
        
        document.getElementById('editForm').action = '{{ route('admin.seminarjenis.aspects.update', [$seminarJenis, '__ID__']) }}'.replace('__ID__', aspect.id);
        document.getElementById('editModal').classList.remove('hidden');
    }

    const typeAdd = document.getElementById('type_add');
    const evalAdd = document.getElementById('evaluator_type_add');
    if (typeAdd && evalAdd) {
        const up = () => updateAspectList(evalAdd.value, 'related_aspects_container_add', 'aspect_list_add');
        typeAdd.addEventListener('change', up);
        evalAdd.addEventListener('change', up);
    }

    const typeEdit = document.getElementById('edit_type');
    if(typeEdit) {
        typeEdit.addEventListener('change', () => {
             if(window._editingAspect) updateAspectList(window._editingAspect.evaluator_type, 'related_aspects_container_edit', 'aspect_list_edit', window._editingAspect.related_aspects || []);
        });
    }

    window.closeEditModal = function() {
        document.getElementById('editModal').classList.add('hidden');
    }

    function initAspectsPage() {
        const modal = document.getElementById('editModal');
        if (!modal || modal.dataset.initialized === 'true') return;

        modal.addEventListener('click', function(e) {
            if (e.target === this) closeEditModal();
        });

        modal.dataset.initialized = 'true';
    }

    if (document.readyState !== 'loading') initAspectsPage();
    else document.addEventListener('DOMContentLoaded', initAspectsPage);
    window.addEventListener('page-loaded', initAspectsPage);
})();
</script>
@endsection
