@extends('admin.layout')

@section('content')
    <div class="row page-titles mx-0">
        <div class="col p-md-0">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">Home</a></li>
                <li class="breadcrumb-item active"><a href="javascript:void(0)">Event List</a></li>
            </ol>
        </div>
    </div>
    <!-- row -->
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <div class="action-section row">
                            <div class="col-lg-8 col-md-8 col-sm-12">
                                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#eventModal" id="AddEventBtn"><i class="fa fa-plus" aria-hidden="true"></i></button>
                            </div>
                            {{-- <div class="custom-tab-1 col-lg-4">
                                <ul class="nav nav-tabs nav-fill">
                                    <li class="nav-item event_page_tabs" data-tab="all_user_tab"><a class="nav-link active show" data-toggle="tab" href="">All</a>
                                    </li>
                                    <li class="nav-item event_page_tabs" data-tab="active_user_tab"><a class="nav-link" data-toggle="tab" href="">Active</a>
                                    </li>
                                    <li class="nav-item event_page_tabs" data-tab="deactive_user_tab"><a class="nav-link" data-toggle="tab" href="">Deactive</a>
                                    </li>
                                </ul>
                            </div> --}}
                        </div>

                        <div class="tab-pane fade show active table-responsive" id="all_user_tab">
                            <table id="all_users" class="table zero-configuration customNewtable" style="width:100%">
                                <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Banner</th>
                                    <th>Title</th>
                                    <th>Fees</th>
                                    <th>Start</th>
                                    <th>End</th>
                                    <th>Created Date</th>
                                    <th>Other</th>
                                </tr>
                                </thead>
                                <tfoot>
                                <tr>
                                    <th>No</th>
                                    <th>Banner</th>
                                    <th>Title</th>
                                    <th>Fees</th>
                                    <th>Start</th>
                                    <th>End</th>
                                    <th>Created Date</th>
                                    <th>Other</th>
                                </tr>
                                </tfoot>
                            </table>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="eventModal">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <form class="form-valide" action="" id="userform" method="post" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5 class="modal-title" id="formtitle">Add Event</h5>
                        <button type="button" class="close" data-dismiss="modal"><span>×</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div id="attr-cover-spin" class="cover-spin"></div>
                        {{ csrf_field() }}
                         <input type="hidden" name="form_id" id="form_id" value="{{ generateRandomString() }}">
                         <input type="hidden" name="eventId" id="eventId">
                        <div class="row">
                            <div class="col-xl-5 col-sm-12">
                                <h5>Event Details</h5>
                                <div class="form-group col-sm-12">
                                    <label class="col-form-label" for="event_image">Event Banner</label>
                                    <input type="file" class="form-control-file" id="event_image" onchange="" name="event_image">
                                    <div id="event_image-error" class="invalid-feedback animated fadeInDown" style="display: none;"></div>
                                    <img src="{{ asset('images/form-user.png') }}" class="" id="event_image_show" height="50px" width="50px" style="margin-top: 12px;width:50px;">
                                </div>
                                <div class="form-group col-sm-12">
                                    <label class="col-form-label" for="event_title">Event Title <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control input-flat" id="event_title" name="event_title" placeholder="">
                                    <div id="event_title-error" class="invalid-feedback animated fadeInDown" style="display: none;"></div>
                                </div>
                                <div class="form-group col-sm-12">
                                    <label class="col-form-label" for="event_description">About Event <span class="text-danger">*</span></label>
                                    <textarea id="event_description" name="event_description" rows="5" class="form-control"></textarea>
                                    <div id="event_description-error" class="invalid-feedback animated fadeInDown" style="display: none;"></div>
                                </div>
                                <div class="form-group col-sm-12">
                                    <label class="col-form-label" for="gender">Who can Participant in this event?
                                    </label>
                                    <div>
                                        <label class="radio-inline mr-3"><input type="radio" name="gender" value="2" checked> Male</label>
                                        <label class="radio-inline mr-3"><input type="radio" name="gender" value="1"> Female</label>
                                        <label class="radio-inline mr-3"><input type="radio" name="gender" value="3"> Both</label>
                                    </div>
                                    <div id="gender-error" class="invalid-feedback animated fadeInDown" style="display: none;"></div>
                                </div>
                            </div>
                            <div class="col-xl-7 col-sm-12">
                                <h5>Event Time</h5>
                                <div class="form-group col-sm-12 row">
                                    <div class="col-md-6 col-sm-12">
                                        <label class="col-form-label" for="eventStartTime">Event Start Time <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group">
                                            <input type="datetime-local" class="form-control " id="eventStartTime" name="eventStartTime" placeholder="yyyy-mm-dd" data-date-format="yyyy-mm-dd" > <span class="input-group-append"><span class="input-group-text"><i class="mdi mdi-calendar-check"></i></span></span>
                                            <div id="eventStartTime-error" class="invalid-feedback animated fadeInDown" style="display: none;"></div>
                                        </div>
                                    </div>
                                    <div class="form-group col-md-6 col-sm-12">
                                        <label class="col-form-label" for="eventEndTime">Event End Time <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group">
                                            <input type="datetime-local" class="form-control " id="eventEndTime" name="eventEndTime" placeholder="yyyy-mm-dd" data-date-format="yyyy-mm-dd"> <span class="input-group-append"><span class="input-group-text"><i class="mdi mdi-calendar-check"></i></span></span>
                                            <div id="eventEndTime-error" class="invalid-feedback animated fadeInDown" style="display: none;"></div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                                <h5>Event Fees</h5>
                                <form class="form-valide" action="" id="eventfreeform" method="post" enctype="multipart/form-data">
                                    {{ csrf_field() }}
                                <div id="dynamicAddRemove" class="">
                                    
                                    {{-- <div class="form-group col-sm-12 row">
                                        <div class="col-md-3 col-sm-12">
                                            <label class="col-form-label" for="fromAge">From Age <span class="text-danger">*</span>
                                            </label>
                                            <input type="number" class="form-control input-flat" id="fromAge1" name="fromAge" placeholder="">
                                            <div id="fromAge-error" class="invalid-feedback animated fadeInDown" style="display: none;"></div>
                                        </div>
                                        <div class="col-md-3 col-sm-12">
                                            <label class="col-form-label" for="toAge">To Age <span class="text-danger">*</span>
                                            </label>
                                            <input type="number" class="form-control input-flat" id="toAge1" name="toAge" placeholder="">
                                            <div id="toAge-error" class="invalid-feedback animated fadeInDown" style="display: none;"></div>
                                        </div>
                                        <div class="col-md-3 col-sm-12">
                                            <label class="col-form-label" for="eventFee">Fee <span class="text-danger">*</span>
                                            </label>
                                            <input type="number" class="form-control input-flat" id="eventFee1" name="eventFee" placeholder="">
                                            <div id="eventFee-error" class="invalid-feedback animated fadeInDown" style="display: none;"></div>
                                        </div>
                                        <div class="col-md-3 col-sm-12">
                                            <label class="col-form-label"></label>
                                            <button type="button" name="add" id="addMoreBtn" class="btn btn-outline-primary" style="margin-top: 12px;">Add More</button>
                                        </div>
                                    </div> --}}
                                </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        
                        <button type="button" class="btn btn-outline-primary" id="save_newEventBtn">Save & New <i class="fa fa-circle-o-notch fa-spin loadericonfa" style="display:none;"></i></button>
                        <button type="button" class="btn btn-primary" id="save_closeEventBtn">Save & Close <i class="fa fa-circle-o-notch fa-spin loadericonfa" style="display:none;"></i></button>
                    </div>
              
            </div>
        </div>
    </div>

    
    <div class="modal fade" id="ScannerUserModal">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">            
                <form class="form-valide" action="" id="scanneruserform" method="post" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5 class="modal-title" id="formtitle">Add Scanner User</h5>
                        <button type="button" class="close" data-dismiss="modal"><span>×</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div id="attr-cover-spin" class="cover-spin"></div>
                        {{ csrf_field() }}
                         <input type="hidden" name="event_id" id="event_id">
                        <div class="row">
                            <div class="col-xl-12 col-sm-12">
                                <div class="form-group row">
                                    <label class="col-lg-12 col-form-label" for="scanner_user">Scanner User <span class="text-danger">*</span></label>
                                    <div class="col-lg-12">
                                        <select class="form-control scanner_user" id="scanner_user" name="scanner_user[]" multiple>
                                            <option></option>
                                            @foreach($usersArr as $user)
                                                <?php 
                                                $full_name = "";
                                                if(isset($user['first_name'])){
                                                    $full_name = $user['first_name'];
                                                }
                                                if(isset($user['middle_name']) && !empty($user['middle_name'])){
                                                    $full_name .= ' '.$user['middle_name'];
                                                }
                                                if(isset($user['last_name']) && !empty($user['last_name'])){
                                                    $full_name .= ' '.$user['last_name'];
                                                }
                                                if(isset($user['mobile_no']) && !empty($user['mobile_no'])){
                                                    $full_name .= ' ['.$user['mobile_no'].']';
                                                }
                                                   //$variant_terms = \App\Models\ProductVariantVariant::where('product_id',$product->id)->where('attribute_term_id',$product_variant)->get()->pluck('product_variant_id');
                                                  
                                                   //$variant_term = \App\Models\ProductVariantVariant::WhereIn('product_variant_id',$variant_terms)->get()->pluck('attribute_term_id')->toArray(); 
                                                ?>
                                                {{-- <option value="{{ $term['id'] }}" @if(isset($variant_term) && in_array($term['id'], $variant_term) ) selected @endif >{{ $term['attrterm_name'] }}</option> --}}
                                                <option value="{{ $user['id'] }}" >{{ $full_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        
                        <button type="button" class="btn btn-primary" id="save_closeScannerUserBtn">Save & Close <i class="fa fa-circle-o-notch fa-spin loadericonfa" style="display:none;"></i></button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="DeleteEventModal">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Remove Event</h5>
                </div>
                <div class="modal-body">
                    Are you sure you wish to remove this Event?
                </div>
                <div class="modal-footer">
                    <button class="btn btn-default" data-dismiss="modal" type="button">Cancel</button>
                    <button class="btn btn-danger" id="RemoveEventSubmit" type="submit">Remove <i class="fa fa-circle-o-notch fa-spin removeloadericonfa" style="display:none;"></i></button>
                </div>
            </div>
        </div>
    </div>
 <?php 
    function generateRandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
?>
@endsection

@section('js')
<!-- user list JS start -->
<script type="text/javascript">
    $(document).ready(function() {

        $('.scanner_user').select2({
            width: '100%',
            multiple: true,
            placeholder: "Select...",
            allowClear: true,
            autoclose: false,
            closeOnSelect: false,
        });

        event_page_tabs(true);
        var i = 1;
        // $("#addMoreBtn").click(function () {
        $('body').on('click', '#addMoreBtn', function () {    

        var formData = new FormData($("#eventfreeform")[0]);
        var valid_variants = validateForm();
        var form_id = $("#form_id").val();
        formData.append('form_id',form_id);
    
        if(valid_variants==true){
            $.ajax({
                type: 'POST',
                url: "{{ url('admin/addorupdateeventfree') }}",
                data: formData,
                processData: false,
                contentType: false,
                success: function (res) {
                    if(res.status == 'failed'){
                       
                        if (res.errors.fromAge) {
                            $('#fromAge-error').show().text(res.errors.fromAge);
                        } else {
                            $('#fromAge-error').hide();
                        }

                        if (res.errors.toAge) {
                            $('#toAge-error').show().text(res.errors.toAge);
                        } else {
                            $('#toAge-error').hide();
                        }

                        if (res.errors.eventFee) {
                            $('#eventFee-error').show().text(res.errors.eventFee);
                        } else {
                            $('#eventFee-error').hide();
                        }
                        
                    }

                    if(res.status == 200){
                        $("#eventModal").find('#eventfreeform').trigger('reset');
                        $("#fromAge-error").html("");
                        $("#toAge-error").html("");
                        $("#eventFee-error").html("");
                        $("#fromAge").focus();
                    
                        toastr.success("Event Free  Added",'Success',{timeOut: 5000});
                        
                        $("#dynamicAddRemove").append('<div class="newAddedRowBox form-group col-sm-12 row"><div class="col-md-3 col-sm-12"><label class="col-form-label" >From Age <span class="text-danger">*</span></label><input type="text" class="form-control input-flat fromAge" readonly value="'+res.data.from_age+'"  placeholder=""><div class="invalid-feedback animated fadeInDown" style="display: none;"></div></div><div class="col-md-3 col-sm-12"><label class="col-form-label" >To Age <span class="text-danger">*</span></label><input type="text" class="form-control input-flat toAge" readonly  value="'+res.data.to_age+'" placeholder=""><div id="toAge-error" class="invalid-feedback animated fadeInDown" style="display: none;"></div></div><div class="col-md-3 col-sm-12"><label class="col-form-label">Fee <span class="text-danger">*</span></label><input type="text" class="form-control input-flat eventFee" readonly  value="'+res.data.fees+'" placeholder=""><div  class="invalid-feedback animated fadeInDown" style="display: none;"></div></div><div class="col-md-3 col-sm-12"><label class="col-form-label"></label><button type="button" data-id="'+res.data.id+'" class="btn btn-outline-danger remove-input-field" style="margin-top: 35px;">Delete</button></div></div>');
                        
                    }

                    if(res.status == 400){
                      
                        toastr.error(res.message,'Error',{timeOut: 5000});
                    }
                },
                error: function (data) {
                  
                    toastr.error("Please try again",'Error',{timeOut: 5000});
                }
            });
        }else{
            // $(btn).prop('disabled',false);
            // $(btn).find('.loadericonfa').hide();
           
        }

            

        });

        $(document).on('click', '.remove-input-field', function () {
            $(this).parents('div.newAddedRowBox').remove();
            var remove_eventfreeId = $(this).attr('data-id');
            $.ajax({
                type: 'GET',
                url: "{{ url('admin/eventfree') }}" +'/' + remove_eventfreeId +'/delete',
                success: function (res) {
                    if(res.status == 200){
                        toastr.success("Event Free Deleted",'Success',{timeOut: 5000});
                    }
                    if(res.status == 400){
                        
                        toastr.error("Please try again",'Error',{timeOut: 5000});
                    }
                },
                error: function (data) {
                   
                    toastr.error("Please try again",'Error',{timeOut: 5000});
                }
            });
        });
    });



    function save_user(btn,btn_type){
        $(btn).prop('disabled',true);
        $(btn).find('.loadericonfa').show();

        var action  = $(btn).attr('data-action');

        var formData = new FormData($("#userform")[0]);

        formData.append('action',action);
        var valid_variants = validateForm();
    
        if(valid_variants==true){
            $.ajax({
                type: 'POST',
                url: "{{ url('admin/addorupdateevent') }}",
                data: formData,
                processData: false,
                contentType: false,
                success: function (res) {
                    if(res.status == 'failed'){
                        $(btn).prop('disabled',false);
                        $(btn).find('.loadericonfa').hide();
                        if (res.errors.event_image) {
                            $('#event_image-error').show().text(res.errors.event_image);
                        } else {
                            $('#event_image-error').hide();
                        }

                        if (res.errors.event_title) {
                            $('#event_title-error').show().text(res.errors.event_title);
                        } else {
                            $('#event_title-error').hide();
                        }

                        if (res.errors.event_description) {
                            $('#event_description-error').show().text(res.errors.event_description);
                        } else {
                            $('#event_description-error').hide();
                        }

                        if (res.errors.eventStartTime) {
                            $('#eventStartTime-error').show().text(res.errors.eventStartTime);
                        } else {
                            $('#eventStartTime-error').hide();
                        }

                        if (res.errors.eventEndTime) {
                            $('#eventEndTime-error').show().text(res.errors.eventEndTime);
                        } else {
                            $('#eventEndTime-error').hide();
                        }

                        if (res.errors.fromAge) {
                            $('#fromAge-error').show().text(res.errors.fromAge);
                        } else {
                            $('#fromAge-error').hide();
                        }

                        if (res.errors.toAge) {
                            $('#toAge-error').show().text(res.errors.toAge);
                        } else {
                            $('#toAge-error').hide();
                        }

                        if (res.errors.eventFee) {
                            $('#eventFee-error').show().text(res.errors.eventFee);
                        } else {
                            $('#eventFee-error').hide();
                        }
                        
                    }
                    
                    if(res.status == 200){
                        
                        if(btn_type == 'save_close'){
                            $("#eventModal").modal('hide');
                            $(btn).prop('disabled',false);
                            $(btn).find('.loadericonfa').hide();
                            $("#userform").trigger('reset');
                            if(res.action == 'add'){
                                event_page_tabs(true);
                                toastr.success("Event has been Successfully Added",'Success',{timeOut: 5000});
                            }
                            if(res.action == 'update'){
                                event_page_tabs();
                                toastr.success("Event has been Successfully Updated",'Success',{timeOut: 5000});
                            }
                        }

                        if(btn_type == 'save_new'){
                            $(btn).prop('disabled',false);
                            $(btn).find('.loadericonfa').hide();
                            $("#userform").trigger('reset');
                            $("#eventModal").find("#save_newEventBtn").removeAttr('data-action');
                            $("#eventModal").find("#save_closeEventBtn").removeAttr('data-action');
                            $("#eventModal").find("#save_newEventBtn").removeAttr('data-id');
                            $("#eventModal").find("#save_closeEventBtn").removeAttr('data-id');
                            $('#eventId').val("");
                            $('#event_image-error').html("");
                            $('#event_title-error').html("");
                            $('#event_description-error').html("");
                            $("#eventStartTime-error").html("");
                            $("#eventEndTime-error").html("");
                            $("#fromAge-error").html("");
                            $("#toAge-error").html("");
                            $("#eventFee-error").html("");
                            var default_image = "{{ asset('images/form-user.png') }}";
                            $('#event_image_show').attr('src', default_image);
                            $("#event_title").focus();
                            if(res.action == 'add'){
                                event_page_tabs(true);
                                toastr.success("Event Added",'Success',{timeOut: 5000});
                            }
                            if(res.action == 'update'){
                                event_page_tabs();
                                toastr.success("User Updated",'Success',{timeOut: 5000});
                            }
                        }
                    }

                    if(res.status == 400){
                        $("#eventModal").modal('hide');
                        $(btn).prop('disabled',false);
                        $(btn).find('.loadericonfa').hide();
                        event_page_tabs();
                        toastr.error("Please try again",'Error',{timeOut: 5000});
                    }
                },
                error: function (data) {
                    $("#eventModal").modal('hide');
                    $(btn).prop('disabled',false);
                    $(btn).find('.loadericonfa').hide();
                    event_page_tabs();
                    toastr.error("Please try again",'Error',{timeOut: 5000});
                }
            });
        }else{
            $(btn).prop('disabled',false);
            $(btn).find('.loadericonfa').hide();
           
        }
    }

    $('body').on('click', '#save_newEventBtn', function () {
        save_user($(this),'save_new');
    });

    $('body').on('click', '#save_closeEventBtn', function () {
        save_user($(this),'save_close');
    });

    $('#eventModal').on('shown.bs.modal', function (e) {
        $("#event_title").focus();
    });


    function save_scanner_user(btn,btn_type){
        $(btn).prop('disabled',true);
        $(btn).find('.loadericonfa').show();

        var action  = $(btn).attr('data-action');

        var formData = new FormData($("#scanneruserform")[0]);

        formData.append('action',action);
        
        $.ajax({
            type: 'POST',
            url: "{{ url('admin/addorupdatescanneruser') }}",
            data: formData,
            processData: false,
            contentType: false,
            success: function (res) {
                if(res.status == 'failed'){
                    $(btn).prop('disabled',false);
                    $(btn).find('.loadericonfa').hide();  
                }

                if(res.status == 200){
                    if(btn_type == 'save_close'){
                        $("#ScannerUserModal").modal('hide');
                        $(btn).prop('disabled',false);
                        $(btn).find('.loadericonfa').hide();
                        event_page_tabs(true);
                        toastr.success("Event User been Successfully Updated",'Success',{timeOut: 5000});
                        
                    }
                }

                if(res.status == 400){
                    $("#ScannerUserModal").modal('hide');
                    $(btn).prop('disabled',false);
                    $(btn).find('.loadericonfa').hide();
                    event_page_tabs();
                    toastr.error("Please try again",'Error',{timeOut: 5000});
                }
            },
            error: function (data) {
                $("#ScannerUserModal").modal('hide');
                $(btn).prop('disabled',false);
                $(btn).find('.loadericonfa').hide();
                event_page_tabs();
                toastr.error("Please try again",'Error',{timeOut: 5000});
            }
        });
       
    }

    $('body').on('click', '#save_newScannerUserBtn', function () {
        save_scanner_user($(this),'save_new');
    });

    $('body').on('click', '#save_closeScannerUserBtn', function () {
        save_scanner_user($(this),'save_close');
    });

    $('#eventModal').on('shown.bs.modal', function (e) {
        $("#event_title").focus();
    });

    $('#event_image').change(function(){
        $('#event_image-error').hide();
        var file = this.files[0];
        var fileType = file["type"];
        var validImageTypes = ["image/jpeg", "image/png", "image/jpg"];
        if ($.inArray(fileType, validImageTypes) < 0) {
            $('#event_image-error').show().text("Please provide a Valid Extension Image(e.g: .jpg .png)");
            var default_image = "{{ asset('images/form-user.png') }}";
            $('#event_image_show').attr('src', default_image);
        }
        else {
            let reader = new FileReader();
            reader.onload = (e) => {
                $('#event_image_show').attr('src', e.target.result);
            }
            reader.readAsDataURL(this.files[0]);
        }
    });

    $('#eventModal').on('hidden.bs.modal', function(){
        $(this).find('form').trigger('reset');
        $(this).find("#save_newEventBtn").removeAttr('data-action');
        $(this).find("#save_closeEventBtn").removeAttr('data-action');
        $(this).find("#save_newEventBtn").removeAttr('data-id');
        $(this).find("#save_closeEventBtn").removeAttr('data-id');
        $('#eventId').val("");
        $('#event_image-error').html("");
        $('#event_title-error').html("");
        $('#event_description-error').html("");
        $('#eventStartTime-error').html("");
        $('#eventEndTime-error').html("");
        $('#gender-error').html("");
        var default_image = "{{ asset('images/form-user.png') }}";
        $('#event_image_show').attr('src', default_image);
    });

    $('#DeleteEventModal').on('hidden.bs.modal', function () {
        $(this).find("#RemoveEventSubmit").removeAttr('data-id');
    });

    function event_page_tabs(is_clearState=false) {
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
                "url": "{{ url('admin/alleventslist') }}",
                "dataType": "json",
                "type": "POST",
                "data":{ _token: '{{ csrf_token() }}'},
                // "dataSrc": ""
            },
            'order': [[ 6, "DESC" ]],
            'columnDefs': [
                { "width": "8%", "targets": 0 },
                { "width": "18%", "targets": 1 },
                { "width": "17%", "targets": 2 },
                { "width": "15%", "targets": 3 },
                { "width": "10%", "targets": 4 },
                { "width": "10%", "targets": 5 },
                { "width": "12%", "targets": 6 },
                { "width": "10%", "targets": 7 },
            ],
            "columns": [
                {data: 'id', name: 'id', class: "text-center", orderable: false,
                    render: function (data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                },
                {data: 'banner', name: 'banner', class: "text-left"},
                {data: 'title', name: 'title', class: "text-left", orderable: false},
                {data: 'fees', name: 'fees', class: "text-left multirow", orderable: false},
                {data: 'startDate', name: 'startDate', class: "text-left multirow", orderable: false},
                {data: 'endDate', name: 'endDate', orderable: false, searchable: false, class: "text-center"},
                {data: 'created_at', name: 'created_at', searchable: false, class: "text-left"},
                {data: 'action', name: 'action', orderable: false, searchable: false, class: "text-center"},
            ]
        });
    }

    
    $('body').on('click', '#AddEventBtn', function (e) {
        $("#eventModal").find('.modal-title').html("Add Event");

        $('#form_id').val(Date.now());
        $("#dynamicAddRemove").html('<div class="newAddedRowBox form-group col-sm-12 row"><div class="col-md-3 col-sm-12"><label class="col-form-label" for="fromAge">From Age <span class="text-danger">*</span></label><input type="text" class="form-control input-flat fromAge" id="fromAge" name="fromAge" placeholder=""><div id="fromAge-error" class="invalid-feedback animated fadeInDown" style="display: none;"></div></div><div class="col-md-3 col-sm-12"><label class="col-form-label" for="toAge">To Age <span class="text-danger">*</span></label><input type="text" class="form-control input-flat toAge" id="toAge" name="toAge" placeholder=""><div id="toAge-error" class="invalid-feedback animated fadeInDown" style="display: none;"></div></div><div class="col-md-3 col-sm-12"><label class="col-form-label" for="eventFee">Fee <span class="text-danger">*</span></label><input type="text" class="form-control input-flat eventFee" id="eventFee" name="eventFee" placeholder=""><div id="eventFee-error" class="invalid-feedback animated fadeInDown" style="display: none;"></div></div><div class="col-md-3 col-sm-12"><label class="col-form-label"></label><button type="button" name="add" id="addMoreBtn" class="btn btn-outline-primary" style="margin-top: 12px;">Add More</button></div></div>');
    });

    $('body').on('click', '#addScannerUser', function () {
        var eventId = $(this).attr('data-id');
        $('#event_id').val(eventId);
        $.get("{{ url('admin/scanneruser') }}" +'/' + eventId +'/edit', function (data) {
            
            $('#scanner_user').val(data).trigger('change'); 
           
        })
        
    });

    $('body').on('click', '#editEventBtn', function () {
        var eventId = $(this).attr('data-id');
        $("#dynamicAddRemove").html('');
        $("#dynamicAddRemove").html('<div class="newAddedRowBox form-group col-sm-12 row"><div class="col-md-3 col-sm-12"><label class="col-form-label" for="fromAge">From Age <span class="text-danger">*</span></label><input type="text" class="form-control input-flat fromAge" id="fromAge" name="fromAge" placeholder=""><div id="fromAge-error" class="invalid-feedback animated fadeInDown" style="display: none;"></div></div><div class="col-md-3 col-sm-12"><label class="col-form-label" for="toAge">To Age <span class="text-danger">*</span></label><input type="text" class="form-control input-flat toAge" id="toAge" name="toAge" placeholder=""><div id="toAge-error" class="invalid-feedback animated fadeInDown" style="display: none;"></div></div><div class="col-md-3 col-sm-12"><label class="col-form-label" for="eventFee">Fee <span class="text-danger">*</span></label><input type="text" class="form-control input-flat eventFee" id="eventFee" name="eventFee" placeholder=""><div id="eventFee-error" class="invalid-feedback animated fadeInDown" style="display: none;"></div></div><div class="col-md-3 col-sm-12"><label class="col-form-label"></label><button type="button" name="add" id="addMoreBtn" class="btn btn-outline-primary" style="margin-top: 12px;">Add More</button></div></div>');
        $.get("{{ url('admin/events') }}" +'/' + eventId +'/edit', function (data) {
            
            $('#eventModal').find('.modal-title').html("Edit Event");
            $('#eventModal').find('#save_closeEventBtn').attr("data-action","update");
            $('#eventModal').find('#save_newEventBtn').attr("data-action","update");
            $('#eventModal').find('#save_closeEventBtn').attr("data-id",eventId);
            $('#eventModal').find('#save_newEventBtn').attr("data-id",eventId);
            $('#eventId').val(data.id);
            if(data.event_image == null){
                var default_image = "{{ asset('images/form-user.png') }}";
                $('#event_image_show').attr('src', default_image);
            }
            else{
                var event_image =  "{{ url('/images/event_image/')}}"+"/"+data.event_image;
                $('#event_image_show').attr('src', event_image);
            }
            $('#event_title').val(data.event_title);
            $('#event_description').val(data.event_description);
            $('#eventStartTime').val(data.event_start_time);
            $('#eventEndTime').val(data.event_end_time);
            $("input[name=gender][value=" + data.event_type + "]").prop('checked', true);

            $.each(data.event_fees, function( key, value ) {
                $('#form_id').val(value.form_id);
                $("#dynamicAddRemove").append('<div class="newAddedRowBox form-group col-sm-12 row"><div class="col-md-3 col-sm-12"><label class="col-form-label" >From Age <span class="text-danger">*</span></label><input type="text" class="form-control input-flat " readonly value="'+value.from_age+'" placeholder=""><div  class="invalid-feedback animated fadeInDown" style="display: none;"></div></div><div class="col-md-3 col-sm-12"><label class="col-form-label" >To Age <span class="text-danger">*</span></label><input type="text" class="form-control input-flat "  readonly value="'+value.to_age+'"  placeholder=""><div  class="invalid-feedback animated fadeInDown" style="display: none;"></div></div><div class="col-md-3 col-sm-12"><label class="col-form-label" ">Fee <span class="text-danger">*</span></label><input type="text" class="form-control input-flat "  readonly value="'+value.fees+'" placeholder=""><div  class="invalid-feedback animated fadeInDown" style="display: none;"></div></div><div class="col-md-3 col-sm-12"><label class="col-form-label"></label><button type="button" name="remove" data-id="'+value.id+'" class="btn btn-outline-danger remove-input-field" style="margin-top: 35px;">Delete   </button></div></div>'); 
            });
        })
    });

    $('body').on('click', '#deleteEventBtn', function (e) {
        // e.preventDefault();
        var delete_eventId = $(this).attr('data-id');
        $("#DeleteEventModal").find('#RemoveEventSubmit').attr('data-id',delete_eventId);
       
    });

    $('body').on('click', '#RemoveEventSubmit', function (e) {
        $('#RemoveEventSubmit').prop('disabled',true);
        $(this).find('.removeloadericonfa').show();
        e.preventDefault();
        var remove_eventId = $(this).attr('data-id');
        $.ajax({
            type: 'GET',
            url: "{{ url('admin/events') }}" +'/' + remove_eventId +'/delete',
            success: function (res) {
                if(res.status == 200){
                    $("#DeleteEventModal").modal('hide');
                    $('#RemoveEventSubmit').prop('disabled',false);
                    $("#RemoveEventSubmit").find('.removeloadericonfa').hide();
                    event_page_tabs();
                    toastr.success("Event Deleted",'Success',{timeOut: 5000});
                }

                if(res.status == 400){
                    $("#DeleteEventModal").modal('hide');
                    $('#RemoveEventSubmit').prop('disabled',false);
                    $("#RemoveEventSubmit").find('.removeloadericonfa').hide();
                    event_page_tabs();
                    toastr.error("Please try again",'Error',{timeOut: 5000});
                }
            },
            error: function (data) {
                $("#DeleteEventModal").modal('hide');
                $('#RemoveEventSubmit').prop('disabled',false);
                $("#RemoveEventSubmit").find('.removeloadericonfa').hide();
                event_page_tabs();
                toastr.error("Please try again",'Error',{timeOut: 5000});
            }
        });
    });

 

    function validateForm() {

        var valid = true;
      
        $('#userform').each(function () {
            var this_form = $(this);
            if (!$(this).valid()) {
                valid = false;
            }
            

            // $(this).find('.fromAge').each(function() {
            //     var thi = $(this);
            //     var this_err = $(thi).attr('id') + "-error";
            //     if($(thi).val()=="" || $(thi).val()==null) {
            //         $(this_form).find("#"+this_err).html("Please provide a value");
            //         $(this_form).find("#"+this_err).show();
            //         valid = false;
            //     }
            // }) 

            // $(this).find('.toAge').each(function() {
            //     var thi = $(this);
            //     var this_err = $(thi).attr('id') + "-error";
            //     if($(thi).val()=="" || $(thi).val()==null) {
            //         $(this_form).find("#"+this_err).html("Please provide a value");
            //         $(this_form).find("#"+this_err).show();
            //         valid = false;
            //     }
            // })

            // $(this).find('.eventFee').each(function() {
            //     var thi = $(this);
            //     var this_err = $(thi).attr('id') + "-error";
            //     if($(thi).val()=="" || $(thi).val()==null) {
            //         $(this_form).find("#"+this_err).html("Please provide a value");
            //         $(this_form).find("#"+this_err).show();
            //         valid = false;
            //     }
            // })

            $(this).find('.fromAge').each(function(key) {
                var thi = $(this);
                var this_err = $(thi).attr('id') + "-error";
                var fromvalue = $(thi).val();
                var values = $("input[name='toAge']").map(function(){return $(this).val();}).get();
             
                if(Number(values[key]) < Number(fromvalue))
                {
                    $(this_form).find("#"+this_err).html("from age from should be less than to age");
                    $(this_form).find("#"+this_err).show();
                    valid = false;
                }
            })

         
        });

        return valid;
    }
</script>
<!-- user list JS end -->
@endsection

