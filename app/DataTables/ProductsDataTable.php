<?php

namespace App\DataTables;

use App\Models\Product;
use App\Models\CustomField;
use App\Models\CustomFieldGroup;
use App\DataTables\BaseDataTable;
use Illuminate\Database\Eloquent\Model;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Illuminate\Support\Facades\DB;

class ProductsDataTable extends BaseDataTable
{

    private $deleteProductPermission;
    private $editProductPermission;

    public function __construct()
    {
        parent::__construct();
        $this->editProductPermission = user()->permission('edit_product');
        $this->deleteProductPermission = user()->permission('delete_product');
    }

    /**
     * Build DataTable class.
     *
     * @param mixed $query Results from query() method.
     * @return \Yajra\DataTables\DataTableAbstract
     */
    public function dataTable($query)
    {
        $datatables = datatables()->eloquent($query);

        $datatables->addColumn('check', function ($row) {
            return '<input type="checkbox" class="select-table-row" id="datatable-row-' . $row->id . '"  name="datatable_ids[]" value="' . $row->id . '" onclick="dataTableRowCheck(' . $row->id . ')">';
        });

        $datatables->editColumn('name', function ($row) {
            $html = '<a href="' . route('products.show', [$row->id]) . '" class="openRightModal font-weight-bold text-dark" >' . ucfirst($row->name) . '</a>';
            $meta = [];
            if ($row->product_code) {
                $meta[] = '<span class="badge badge-light border text-muted">Code: ' . e($row->product_code) . '</span>';
            }
            if ($row->barcode) {
                $meta[] = '<span class="badge badge-light border text-muted"><i class="fa fa-barcode mr-1"></i>' . e($row->barcode) . '</span>';
            }
            if (!empty($meta)) {
                $html .= '<div class="mt-1">' . implode(' ', $meta) . '</div>';
            }
            return $html;
        });

        $datatables->addColumn('specs', function ($row) {
            $specs = [];
            if ($row->voltage) {
                $specs[] = e($row->voltage);
            }
            if ($row->capacity) {
                $specs[] = e($row->capacity);
            }
            if (empty($specs)) {
                return '<span class="text-muted">--</span>';
            }
            return '<span class="badge badge-light border text-dark font-weight-normal"><i class="fa fa-bolt text-warning mr-1"></i>' . implode(' | ', $specs) . '</span>';
        });

        $datatables->addColumn('category', function ($row) {
            $cat = ($row->category) ? $row->category->category_name : '';
            $subCat = ($row->subCategory) ? $row->subCategory->category_name : '';
            if ($cat && $subCat) {
                return '<span class="font-weight-bold">' . e($cat) . '</span> <small class="text-muted">(' . e($subCat) . ')</small>';
            }
            return $cat ?: ($subCat ?: '<span class="text-muted">--</span>');
        });

        $datatables->editColumn('description', function ($row) {
            return strip_tags($row->description);
        });

        $datatables->addColumn('action', function ($row) {
            if (in_array('client', user_roles())) {
                return '<button type="button" class="btn-secondary rounded f-14 add-product" data-product-id="' . $row->id . '">
                        <i class="fa fa-plus mr-1"></i>
                    ' . __('app.addToCart') . '
                    </button>';
            }

            $action = '<div class="task_view">
                    <div class="dropdown">
                        <a class="task_view_more d-flex align-items-center justify-content-center dropdown-toggle" type="link"
                            id="dropdownMenuLink-' . $row->id . '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="icon-options-vertical icons"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuLink-' . $row->id . '" tabindex="0">';

            $action .= '<a href="' . route('products.show', [$row->id]) . '" class="dropdown-item openRightModal" data-product-id="' . $row->id . '"><i class="fa fa-eye mr-2"></i>' . __('app.view') . '</a>';

            if ($this->editProductPermission == 'all' || ($this->editProductPermission == 'added' && user()->id == $row->added_by)) {
                $action .= '<a class="dropdown-item openRightModal" href="' . route('products.edit', [$row->id]) . '">
                                <i class="fa fa-edit mr-2"></i>
                                ' . trans('app.edit') . '
                            </a>';
            }

            if ($this->deleteProductPermission == 'all' || ($this->deleteProductPermission == 'added' && user()->id == $row->added_by)) {
                $action .= '<a class="dropdown-item delete-table-row" href="javascript:;" data-product-id="' . $row->id . '">
                                <i class="fa fa-trash mr-2"></i>
                                ' . trans('app.delete') . '
                            </a>';
            }

            $action .= '</div>
                    </div>
                </div>';

            return $action;
        });

        $datatables->editColumn('default_image', function ($row) {
            return '<img src="' . $row->image_url . '" class="border rounded height-35" />';
        });

        $datatables->editColumn('allow_purchase', function ($row) {
            if ($row->allow_purchase == 1) {
                return '<i class="fa fa-circle mr-1 text-dark-green f-10"></i>' . __('app.allowed');
            }
            return '<i class="fa fa-circle mr-1 text-red f-10"></i>' . __('app.notAllowed');
        });

        $datatables->editColumn('price', function ($row) {
            if (!is_null($row->taxes)) {
                $totalTax = 0;
                foreach (json_decode($row->taxes) as $tax) {
                    $prodTax = Product::taxbyid($tax)->first();
                    if ($prodTax) {
                        $totalTax = $totalTax + ($row->price * ($prodTax->rate_percent / 100));
                    }
                }
                return currency_format($row->price + $totalTax);
            }
            return currency_format($row->price);
        });

        $datatables->addColumn('origin_type', function ($row) {
            $origin = strtolower($row->origin_type ?? 'local');
            if ($origin === 'imported') {
                return '<span class="badge badge-dark"><i class="fa fa-globe mr-1"></i>Imported</span>';
            }
            return '<span class="badge badge-light border"><i class="fa fa-home mr-1"></i>Local</span>';
        });

        $datatables->addColumn('product_source', function ($row) {
            $class = $row->product_classification ?? 'ready_made';
            $source = $row->product_source ?? ($row->is_serialized ? 'badar_energy' : 'oem');

            if ($class === 'assembly_part') {
                return '<span class="badge badge-primary px-2 py-1"><i class="fa fa-cogs mr-1"></i>Assembly Part</span>';
            }
            if ($source === 'badar_energy') {
                return '<span class="badge badge-success px-2 py-1"><i class="fa fa-bolt mr-1"></i>Badar Energy</span>';
            }
            return '<span class="badge badge-info px-2 py-1"><i class="fa fa-box mr-1"></i>Ready-Made (OEM)</span>';
        });

        $datatables->addColumn('available_stock', function ($row) {
            $totalStock = (float) $row->inventories->sum('quantity');
            if ($totalStock <= 0) {
                return '<span class="badge badge-danger px-2 py-1"><i class="fa fa-times-circle mr-1"></i>0.00 (Out of Stock)</span>';
            }
            if ($totalStock < 10) {
                return '<span class="badge badge-warning px-2 py-1"><i class="fa fa-exclamation-triangle mr-1"></i>' . number_format($totalStock, 2) . ' (Low Stock)</span>';
            }
            return '<span class="badge badge-success px-2 py-1"><i class="fa fa-check-circle mr-1"></i>' . number_format($totalStock, 2) . '</span>';
        });

        $datatables->addIndexColumn();
        $datatables->smart(false);
        $datatables->setRowId(function ($row) {
            return 'row-' . $row->id;
        });

        // Custom Fields For export
        $customFieldColumns = CustomField::customFieldData($datatables, Product::CUSTOM_FIELD_MODEL);

        $datatables->rawColumns(array_merge(['action', 'price', 'allow_purchase', 'check', 'name', 'specs', 'category', 'default_image', 'available_stock', 'origin_type', 'product_source'], $customFieldColumns));

        return $datatables;
    }

    /**
     * @param Product $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Product $model)
    {
        $request = $this->request();

        $model = $model->with('tax', 'category', 'subCategory', 'inventories')
            ->select('id', 'name', 'price', 'taxes', 'allow_purchase', 'added_by', 'default_image', 'category_id', 'sub_category_id', 'description', 'is_serialized', 'origin_type', 'product_classification', 'product_source', 'product_code', 'barcode', 'voltage', 'capacity');

        // Classification & Source Filter
        if (!is_null($request->is_serialized) && $request->is_serialized != 'all') {
            if ($request->is_serialized === 'assembly_part') {
                $model->where('products.product_classification', 'assembly_part');
            } elseif ($request->is_serialized === 'ready_made') {
                $model->where('products.product_classification', 'ready_made');
            } elseif ($request->is_serialized === 'badar_energy') {
                $model->where('products.product_source', 'badar_energy');
            } elseif ($request->is_serialized === 'oem') {
                $model->where('products.product_source', 'oem');
            } elseif ($request->is_serialized == '1' || $request->is_serialized == '0') {
                $model->where('products.is_serialized', $request->is_serialized);
            }
        }

        // Origin Filter
        if (!is_null($request->origin_type) && $request->origin_type != 'all') {
            $model->where('products.origin_type', $request->origin_type);
        }

        // Category Filter
        if (!is_null($request->category_id) && $request->category_id != 'all' && $request->category_id > 0) {
            $model->where('category_id', $request->category_id);
        }

        // Subcategory Filter
        if (!is_null($request->sub_category_id) && $request->sub_category_id != 'all' && $request->sub_category_id > 0) {
            $model->where('sub_category_id', $request->sub_category_id);
        }

        // Unit Type Filter
        if (!is_null($request->unit_type_id) && $request->unit_type_id != 'all') {
            $model->where('unit_id', $request->unit_type_id);
        }

        // Stock Status Filter
        if (!is_null($request->stock_status) && $request->stock_status != 'all') {
            if ($request->stock_status === 'out_of_stock') {
                $model->whereDoesntHave('inventories', function ($q) {
                    $q->where('quantity', '>', 0);
                });
            } elseif ($request->stock_status === 'low_stock') {
                $model->whereHas('inventories', function ($q) {
                    $q->havingRaw('SUM(quantity) > 0 AND SUM(quantity) < 10');
                });
            } elseif ($request->stock_status === 'in_stock') {
                $model->whereHas('inventories', function ($q) {
                    $q->havingRaw('SUM(quantity) >= 10');
                });
            }
        }

        // Search Text Filter
        if ($request->searchText != '') {
            $search = request('searchText');
            $model->where(function ($query) use ($search) {
                $query->where('products.name', 'like', '%' . $search . '%')
                    ->orWhere('products.product_code', 'like', '%' . $search . '%')
                    ->orWhere('products.barcode', 'like', '%' . $search . '%')
                    ->orWhere('products.voltage', 'like', '%' . $search . '%')
                    ->orWhere('products.capacity', 'like', '%' . $search . '%')
                    ->orWhere('products.price', 'like', '%' . $search . '%');
            });
        }

        if (user()->permission('view_product') == 'added') {
            $model->where('products.added_by', user()->id);
        }

        if (in_array('client', user_roles())) {
            $model->where('products.allow_purchase', 1);
        }

        return $model;
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        return $this->setBuilder('products-table', 2)
            ->parameters([
                'initComplete' => 'function () {
                   window.LaravelDataTables["products-table"].buttons().container()
                    .appendTo( "#table-actions")
                }',
                'fnDrawCallback' => 'function( oSettings ) {
                    $("body").tooltip({
                        selector: \'[data-toggle="tooltip"]\'
                    })
                }',
            ])
            ->buttons(Button::make(['extend' => 'excel', 'text' => '<i class="fa fa-file-export"></i> ' . trans('app.exportExcel')]));
    }

    /**
     * Get columns.
     *
     * @return array
     */
    protected function getColumns()
    {
        $data = [
            'check' => [
                'title' => '<input type="checkbox" name="select_all_table" id="select-all-table" onclick="selectAllTable(this)">',
                'exportable' => false,
                'orderable' => false,
                'searchable' => false,
                'visible' => !in_array('client', user_roles())
            ],
            '#' => ['data' => 'DT_RowIndex', 'orderable' => false, 'searchable' => false, 'visible' => true, 'title' => 'S. No.'],
            __('app.id') => ['data' => 'id', 'name' => 'id', 'title' => __('app.id'), 'visible' => false],
            __('modules.productImage') => ['data' => 'default_image', 'name' => 'default_image', 'title' => __('modules.productImage'), 'exportable' => false,],
            __('app.menu.products') => ['data' => 'name', 'name' => 'name', 'title' => 'Product Details'],
            'category' => ['data' => 'category', 'name' => 'category.category_name', 'title' => 'Category'],
            'origin_type' => ['data' => 'origin_type', 'name' => 'origin_type', 'title' => 'Origin'],
            'product_source' => ['data' => 'product_source', 'name' => 'product_source', 'title' => 'Source / Classification'],
            __('app.price') . ' (' . __('app.inclusiveAllTaxes') . ')' => ['data' => 'price', 'name' => 'price', 'title' => __('app.price')],
            'available_stock' => ['data' => 'available_stock', 'name' => 'available_stock', 'title' => 'Stock Status', 'orderable' => false, 'searchable' => false],
            __('app.purchaseAllow') => ['data' => 'allow_purchase', 'name' => 'allow_purchase', 'visible' => !in_array('client', user_roles()), 'title' => __('app.purchaseAllow')]
        ];

        $action = [
            Column::computed('action', __('app.action'))
                ->exportable(false)
                ->printable(false)
                ->orderable(false)
                ->searchable(false)
                ->addClass('text-right pr-20')
        ];

        return array_merge($data, CustomFieldGroup::customFieldsDataMerge(new Product()), $action);
    }
}
