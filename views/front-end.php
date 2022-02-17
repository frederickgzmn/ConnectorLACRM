<style>
    .marker {
        background-image: url('{{ patch_base }}img/market-icon-24.png');
        background-size: cover;
        width: 50px;
        height: 50px;
        border-radius: 50%;
        cursor: pointer;
    }
</style>

<div class="container_map container-fluid">
    <div class="row text-left">
        <div class="col-3">
            <div class='sidebar'>
                <div class='heading'>
                    <input type="text" class="form-control" id="searchname" value="" placeholder="Search.." aria-label="Search.." aria-describedby="basic-addon1">
                </div>

                <div id='searchlistings' class='listings list-group'></div>
                <div id='listings' class='listings list-group'></div>
            </div>
        </div>

        <div class="col-9 px-0">
            <div id='map' class='map pad2'></div>
        </div>
    </div>


    <!-- Modal -->
    <div class="modal fade" data-backdrop="false" id="myModal" role="dialog">
        <div class="modal-dialog">
            <input type="hidden" value="" id="lat" name="lat">
            <input type="hidden" value="" id="long" name="long">

            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <small class="modal-title">Creating marker</small>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                </div>
                <div class="modal-body">
                    <div class="input-group input-group-sm mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text" id="inputGroup-sizing-sm">Name</span>
                        </div>
                        <input type="text" id="markername" class="form-control" aria-label="Sizing example input" aria-describedby="inputGroup-sizing-sm">
                    </div>

                    <div class="dropdown-divider"></div>

                    <h5 class="card-title">Adding tag</h5>

                    <div class="input-group mb-3">
                        <input name="tagname" id="tagname" type="text" class="form-control" placeholder="Tag's name" aria-label="Tag's name" aria-describedby="tag-add">
                        <div class="input-group-append">
                            <button class="btn btn-success" type="button" id="tag-add">Add</button>
                        </div>
                    </div>

                    <select id="tags" name="tags" multiple="multiple">
                        <option value="">...</option>
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" id="marker-add" class="btn btn-success" data-dismiss="modal">Save Marker</button>
                </div>
            </div>

        </div>
    </div>
</div>

<div class="alert alert-success" id="alertjs" style="display: none;">
    <strong><div id="alertjs"></div></strong>
</div>
