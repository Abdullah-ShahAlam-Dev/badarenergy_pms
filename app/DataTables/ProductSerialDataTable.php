<?php

namespace App\DataTables;

use App\DataTables\BaseDataTable;
use App\Models\ProductSerial;
use Yajra\DataTables\Html\Column;

class ProductSerialDataTable extends BaseDataTable
{
    /**
     * Build DataTable class.
     *
     * @param mixed $query
     * @return \Yajra\DataTables\DataTableAbstract
     */
    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
            ->addColumn('product_name', function ($row) {
                return $row->product ? $row->product->name : '-';
            })
            ->addColumn('warehouse_name', function ($row) {
                return $row->warehouse ? $row->warehouse->name : '-';
            })
            ->editColumn('status', function ($row) {
                return match($row->status) {
                    'available' => '<span class="badge badge-success">Available</span>',
                    'sold' => '<span class="badge badge-info">Sold</span>',
                    'faulty' => '<span class="badge badge-danger">Faulty/Damaged</span>',
                    'in_transit' => '<span class="badge badge-warning">In-Transit</span>',
                    default => '<span class="badge badge-secondary">' . ucfirst($row->status) . '</span>',
                };
            })
            ->editColumn('warranty_expires_at', function ($row) {
                return $row->warranty_expires_at ? $row->warranty_expires_at->format(company()->date_format) : '-';
            })
            ->editColumn('invoice_number', function ($row) {
                return $row->invoice ? '<a href="' . route('invoices.show', $row->invoice_id) . '" class="text-darkest-grey">' . $row->invoice->invoice_number . '</a>' : '-';
            })
            ->addIndexColumn()
            ->smart(false)
            ->setRowId(function ($row) {
                return 'row-' . $row->id;
            })
            ->rawColumns(['status', 'invoice_number', 'product_name', 'warehouse_name']);
    }

    /**
     * Get query source of dataTable.
     *
     * @param ProductSerial $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(ProductSerial $model)
    {
        $request = $this->request();
        $query = ProductSerial::with(['product', 'warehouse', 'invoice'])->select('product_serials.*');

        // Apply filters
        if (!is_null($request->searchText)) {
            $query->where(function ($q) use ($request) {
                $q->where('serial_number', 'like', '%' . $request->searchText . '%')
                  ->orWhereHas('product', function ($q2) use ($request) {
                      $q2->where('name', 'like', '%' . $request->searchText . '%');
                  });
            });
        }

        if ($request->warehouse_id != 'all' && !is_null($request->warehouse_id)) {
            $query->where('product_serials.warehouse_id', $request->warehouse_id);
        }

        if ($request->product_id != 'all' && !is_null($request->product_id)) {
            $query->where('product_serials.product_id', $request->product_id);
        }

        if ($request->status != 'all' && !is_null($request->status)) {
            $query->where('product_serials.status', $request->status);
        }

        if ($request->category_id != 'all' && !is_null($request->category_id)) {
            $query->whereHas('product', function ($q) use ($request) {
                $q->where('category_id', $request->category_id);
            });
        }

        return $query;
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        return $this->setBuilder('serial-table', 2)
            ->parameters([
                'initComplete' => 'function () {
                   window.LaravelDataTables["serial-table"].buttons().container()
                    .appendTo("#table-actions")
                }',
            ])
            ->buttons(\Yajra\DataTables\Html\Button::make(['extend' => 'excel', 'text' => '<i class="fa fa-file-export"></i> ' . trans('app.exportExcel')]));
    }

    /**
     * Get columns.
     *
     * @return array
     */
    protected function getColumns()
    {
        return [
            '#' => ['data' => 'DT_RowIndex', 'orderable' => false, 'searchable' => false, 'visible' => false, 'title' => '#'],
            __('app.id') => ['data' => 'id', 'name' => 'id', 'visible' => false, 'exportable' => false, 'title' => __('app.id')],
            'serial_number' => ['data' => 'serial_number', 'name' => 'serial_number', 'title' => 'Serial Number'],
            'product_name' => ['data' => 'product_name', 'name' => 'product.name', 'title' => 'Product Name', 'exportable' => true, 'orderable' => false],
            'warehouse_name' => ['data' => 'warehouse_name', 'name' => 'warehouse.name', 'title' => 'Location', 'exportable' => true, 'orderable' => false],
            'status' => ['data' => 'status', 'name' => 'status', 'title' => 'Status'],
            'invoice_number' => ['data' => 'invoice_number', 'name' => 'invoice.invoice_number', 'title' => 'Invoice #', 'exportable' => true, 'orderable' => false],
            'warranty_expires_at' => ['data' => 'warranty_expires_at', 'name' => 'warranty_expires_at', 'title' => 'Warranty Expires At'],
        ];
    }
}
