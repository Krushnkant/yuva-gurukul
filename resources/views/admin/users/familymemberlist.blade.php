@extends('admin.layout')

@section('content')
    <div class="row page-titles mx-0">
        <div class="col p-md-0">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">Home</a></li>
                <li class="breadcrumb-item "><a href="{{ url('admin/users') }}">User List</a></li>
                <li class="breadcrumb-item active"><a href="javascript:void(0)">Family Member List</a></li>
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
                            <table id="all_users" class="table zero-configuration customNewtable" style="width:100%">
                                <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Profile</th>
                                    <th>Parent User</th>
                                    <th>Role</th>
                                    <th>Contact Info</th>
                                    <th>Other Info</th>
                                    <th>Verify</th>
                                    <th>Registration Date</th>
                                    
                                </tr>
                                </thead>
                                <tfoot>
                                <tr>
                                    <th>No</th>
                                    <th>Profile</th>
                                    <th>Parent User</th>
                                    <th>Role</th>
                                    <th>Contact Info</th>
                                    <th>Other Info</th>
                                    <th>Verify</th>
                                   
                                    <th>Registration Date</th>
                                    
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
        user_page_tabs('',true);
    });
    function user_page_tabs(tab_type='',is_clearState=false) {
        if(is_clearState){
            $('#all_users').DataTable().state.clear();
        }

        $('#all_users').DataTable({
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
                "url": "{{ url('admin/allfamilymemberuserslist/'.$id) }}",
                "dataType": "json",
                "type": "POST",
                "data":{ _token: '{{ csrf_token() }}' ,tab_type: tab_type},
                // "dataSrc": ""
            },
            'order': [[ 0, "DESC" ]],
            'columnDefs': [
                { "width": "5%", "targets": 0 },
                { "width": "12%", "targets": 1 },
                { "width": "12%", "targets": 2 },
                { "width": "12%", "targets": 3 },
                { "width": "15%", "targets": 4 },
                { "width": "8%", "targets": 5 },
                { "width": "8%", "targets": 6 },
                { "width": "10%", "targets": 7 }
            ],
            "columns": [
                {data: 'id', name: 'id', class: "text-center",
                    render: function (data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                },
                {data: 'profile_pic', name: 'profile_pic', class: "text-left", orderable: false},
                {data: 'parent_profile', name: 'parent_profile', class: "text-left", orderable: false},
                {data: 'role', name: 'role', class: "text-left", orderable: false},
                {data: 'contact_info', name: 'contact_info', class: "text-left multirow", orderable: false},
                {data: 'other_info', name: 'other_info', class: "text-left multirow", orderable: false},
                {data: 'verify', name: 'verify', class: "text-left", orderable: false},
                {data: 'created_at', name: 'created_at', searchable: false, class: "text-left"},
            ]
        });
    }
</script>
<!-- user list JS end -->
@endsection

