# DataTable Partial - Universal Usage Guide

## 📊 Universal DataTable Component

DataTable partial **modul-spesifik deyil**, istənilən modulda istifadə edilə bilər.

---

## 🎯 Struktur

### Controller-də Data Formatlaması

```php
public function index()
{
    // 1. Columns təyin et
    $columns = [
        ['label' => __('ID'), 'width' => '50'],
        ['label' => __('Name')],
        ['label' => __('Email')],
        ['label' => __('Status')],
        ['label' => __('Date'), 'width' => '150'],
    ];
    
    // 2. Data-nı formatla
    $formattedRows = $users->map(function ($user) {
        return [
            'id' => $user->id,
            'cells' => [
                $user->name,
                $user->email,
                $user->status ? __('Active') : __('Inactive'),
                $user->created_at->format('d M Y H:i')
            ]
        ];
    });
    
    return view('module::index', compact('columns', 'formattedRows'));
}
```

### Blade-də Include

```blade
@include('admin.partials.datatable', [
    'tableId' => 'usersTable',
    'columns' => $columns,
    'rows' => $formattedRows,
    'checkboxes' => true,
    'actions' => true,
    'exportButton' => true,
    'exportRoute' => route('admin.users.export'),
    'deleteButton' => true,
    'deleteRoute' => route('admin.users.destroy', ['user' => ':id']),
    'bulkDeleteRoute' => route('admin.users.bulk-delete'),
    'pageLength' => 10,
    'order' => [1, 'desc'],
])
```

---

## 📋 Parametrlər

| Parametr | Type | Default | Açıqlama |
|----------|------|---------|----------|
| `tableId` | string | auto | Table unique ID |
| `columns` | array | [] | Column definitions |
| `rows` | collection | [] | Formatted data rows |
| `checkboxes` | bool | true | Show checkbox column |
| `actions` | bool | true | Show actions column |
| `exportButton` | bool | false | Show export button |
| `exportRoute` | string | null | Export endpoint URL |
| `deleteButton` | bool | false | Show delete button |
| `deleteRoute` | string | null | Delete endpoint (use `:id`) |
| `bulkDeleteRoute` | string | null | Bulk delete endpoint |
| `pageLength` | int | 10 | Items per page |
| `order` | array | [1, 'desc'] | Default sorting |

---

## 📊 Data Format

### Columns Array

```php
$columns = [
    ['label' => __('Column 1'), 'width' => '100'],  // Width optional
    ['label' => __('Column 2')],
    ['label' => __('Column 3')],
];
```

### Rows Array

```php
$rows = [
    [
        'id' => 1,
        'cells' => [
            'Value 1',
            'Value 2',
            '<span class="badge bg-success">Active</span>', // HTML allowed
        ]
    ],
    [
        'id' => 2,
        'cells' => ['Value 1', 'Value 2', 'Value 3']
    ]
];
```

**Vacib:** 
- `cells` sayı `columns` sayı ilə **eyni olmalıdır** (ID və Actions xaric)
- `cells` array-də HTML istifadə edə bilərsiniz (`{!! $cell !!}`)

---

## 🔧 Delete Routes

### Single Delete (`:id` placeholder)

```php
'deleteRoute' => route('admin.module.destroy', ['id' => ':id'])
```

Avtomatik hər row-da `:id` → `$row['id']` ilə əvəzlənir.

### Bulk Delete

```php
public function bulkDelete()
{
    $ids = request('ids', []);
    Model::whereIn('id', $ids)->delete();
    
    return response()->json([
        'success' => true,
        'message' => __('Deleted successfully')
    ]);
}
```

---

## 📦 Export

### Controller

```php
use App\Services\ExcelExportService;

public function export(ExcelExportService $excel)
{
    $headers = [__('ID'), __('Name'), __('Date')];
    
    $data = $items->map(function ($item) {
        return [$item->id, $item->name, $item->created_at];
    });
    
    return $excel->export($data, $headers, 'export-filename');
}
```

---

## 💡 Nümunələr

### Form Module (Çoxlu dinamik column)

```php
$formattedRows = $responses->map(function ($response) use ($labels) {
    $row = ['id' => $response->id, 'cells' => []];
    
    foreach ($labels as $label) {
        $value = $response->getValueForLabel($label->id);
        $row['cells'][] = $value;
    }
    
    return $row;
});
```

### User Module (Sadə)

```php
$formattedRows = $users->map(function ($user) {
    return [
        'id' => $user->id,
        'cells' => [
            $user->name,
            $user->email,
            $user->created_at->format('d M Y')
        ]
    ];
});
```

### Product Module (HTML ilə)

```php
$formattedRows = $products->map(function ($product) {
    return [
        'id' => $product->id,
        'cells' => [
            $product->name,
            '<img src="' . $product->image . '" width="50">',
            '<span class="badge bg-' . $product->statusColor . '">' . $product->status . '</span>',
            '$' . number_format($product->price, 2)
        ]
    ];
});
```

---

## ✅ Üstünlüklər

- ✅ Universal - istənilən modulda işləyir
- ✅ No modul-specific logic
- ✅ Pre-formatted data
- ✅ HTML support in cells
- ✅ Automatic pagination, sorting, search
- ✅ Export to Excel
- ✅ Single & bulk delete
- ✅ Checkbox state management

---

## 🚫 Əvvəlki Problem (Həll Olundu)

**Əvvəl:** DataTable blade-də Form-spesifik `labels` və `labels_data` var idi.

```blade
@if($labels)  ← Form-specific!
    @foreach($labels as $label)
```

**İndi:** Tamamilə universal, pre-formatted data.

```blade
@foreach($row['cells'] as $cell)
    <td>{!! $cell !!}</td>
@endforeach
```

Artıq **hər hansı modulda** istifadə edə bilərsiniz! 🎉
