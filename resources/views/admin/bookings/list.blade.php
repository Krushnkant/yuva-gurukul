@extends('admin.layout')

@section('content')
    <div class="row page-titles mx-0">
        <div class="col p-md-0">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">Home</a></li>
                <li class="breadcrumb-item active"><a href="javascript:void(0)">Booking User List</a></li>
            </ol>
        </div>
    </div>
    <!-- row -->
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                       

                        <div class="tab-pane fade show active table-responsive" id="all_user_tab">
                            <table id="all_bookings" class="table zero-configuration customNewtable" style="width:100%">
                                <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Name</th>
                                    <th>Title</th>
                                    <th>Total Person</th>
                                    <th>Total Amount</th>
                                    <th>Transaction Id</th>
                                    <th>Created Date</th>
                                </tr>
                                </thead>
                                <tfoot>
                                <tr>
                                    <th>No</th>
                                    <th>Name</th>
                                    <th>Title</th>
                                    <th>Total Person</th>
                                    <th>Total Amount</th>
                                    <th>Transaction Id</th>
                                    <th>Created Date</th>
                                </tr>
                                </tfoot>
                            </table>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

   

@endsection

@section('js')
<!-- user list JS start -->
<script type="text/javascript">
  
  $(document).ready(function() {
   
    event_page_tabs(true);
});

    function event_page_tabs(is_clearState=false) {
        if(is_clearState){
            $('#all_bookings').DataTable().state.clear();
        }

        $('#all_bookings').DataTable({
            "destroy": true,
            "processing": true,
            "serverSide": true,
            'stateSave': function(){
                if(is_clearState){
                    return false;
                }
                else{
                    return true;
                }
            },
            "ajax":{
                "url": "{{ url('admin/allbookinglist') }}",
                "dataType": "json",
                "type": "POST",
                "data":{ _token: '{{ csrf_token() }}',event_id:'{{ $id }}' },
                // "dataSrc": ""
            },
            'order': [[ 6, "DESC" ]],
            'columnDefs': [
                { "width": "8%", "targets": 0 },
                { "width": "12%", "targets": 1 },
                { "width": "20%", "targets": 2 },
                { "width": "15%", "targets": 3 },
                { "width": "10%", "targets": 4 },
                { "width": "10%", "targets": 5 },
                { "width": "12%", "targets": 6 },
            ],
            "columns": [
                {data: 'id', name: 'id', class: "text-center", orderable: false,
                    render: function (data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                },
                {data: 'name', name: 'name', class: "text-left", orderable: false},
                {data: 'title', name: 'title', class: "text-left", orderable: false},
                {data: 'total_person', name: 'total_person', class: "text-left multirow", orderable: false},
                {data: 'total_amount', name: 'total_amount', class: "text-left multirow", orderable: false},
                {data: 'payment_transaction_id', name: 'payment_transaction_id', orderable: false, searchable: false, class: "text-center"},
                {data: 'created_at', name: 'created_at', searchable: false, class: "text-left", orderable: false},
            ]
        });
    }
</script>
<!-- user list JS end -->
@endsection

