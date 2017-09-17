<html>
        <head>
                <title>AutoDQM</title>

                <!-- Latest compiled and minified Boostrap CSS and Javascript -->
                <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
                <script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>

                <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
                <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>

                <!-- Slider -->
                <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-slider/9.8.1/css/bootstrap-slider.min.css">
                <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-slider/9.8.1/bootstrap-slider.min.js"></script>
 
                <!-- My Code -->
                
                <!-- CSS -->
                <style>
                    .container-wide {
                        padding: 0 50px !important;
                    }
                    .loader {
                        text-align: center;
                        margin: auto;
                        border: 16px solid #f3f3f3; /* Light grey */
                        border-top: 16px solid #3498db; /* Blue */
                        border-radius: 50%;
                        width: 120px;
                        height: 120px;
                        animation: spin 2s linear infinite;
                    }
                    @keyframes spin {
                        0% { transform: rotate(0deg); }
                        100% { transform: rotate(360deg); }
                    }
                </style>

                <!-- JQuery -->
                <script>
                    // Global variables
                    var full_inp = true;
                    var t0 = 0;

                    // Form functions: mostly for updating 'preview' wells
                    function updt_data() {
                        full_inp = false;
                        $("#full")[0].reset();
                        var sample = document.getElementById("sample").value;
                        var patch = document.getElementById("patch").value;
                        var vers = document.getElementById("vers").value;

                        $('#preview').text("/"+sample+"/"+"CMSSW_"+patch+"_realistic_"+vers+"/DQMIO");
                    }
                    function dataName_full() {
                        full_inp = true;
                        $("#modular")[0].reset();

                        var path = document.getElementById("path").value;

                        $('#preview').text("");
                        $('#preview').text(path);
                    }
                    function updt_ref() {
                        full_inp = false;
                        $("#ref_full")[0].reset();
                        var ref_sample = document.getElementById("ref_sample").value;
                        var ref_patch = document.getElementById("ref_patch").value;
                        var ref_vers = document.getElementById("ref_vers").value;

                        $('#ref_preview').text("/"+ref_sample+"/"+"CMSSW_"+ref_patch+"_realistic_"+ref_vers+"/DQMIO");
                    }
                    function refName_full() {
                        full_inp = true;
                        $("#ref_modular")[0].reset();

                        var ref_path = document.getElementById("ref_path").value;

                        $('#ref_preview').text("");
                        $('#ref_preview').text(ref_path);
                    }

                    // End form functions

                    // Query handlers
                    function handle_response(response) {
                        console.log(response); 
                        console.log("Run time: " + String(Math.floor(Date.now() / 1000) - t0));
                        try {
                            // Handle output from main.py
                            console.log(response["response"]["payload"]);
                            var resp = response["response"];

                            if (resp["status"] == "failed") {
                                $("#internal_err").text(resp["fail_reason"]);
                                $("#submit").show();
                                $("#internal_err").show();
                            }                            

                            else {
                                $("#finished").show();
                            }
                        }
                        catch(TypeError) {
                            // Handle crashes, system error, timeouts, etc.
                            console.log(response["responseText"]);
                            var resp = response["responseText"];
                            var err_msg = "";
                            
                            if (resp.indexOf("504") !== 1) {
                                err_msg = "Error: Gateway timed out. Could not reach server."
                            }
                            else {
                                err_msg = "Error: An internal error occured."
                            }

                            $("#internal_err").text(err_msg);

                            $("#submit").show();
                            $("#internal_err").show();
                        }
                        finally {
                            $("#load").hide();
                        }
                    }

                    function submit(query) {
                        console.log("submitting query");
                        console.log(query);
                        $("#load").show();
                        $("#submit").hide();
                        t0 = Math.floor(Date.now() / 1000);
                        console.log(t0);

                        $.ajaxSetup({timeout:300000}); // Set timeout to 5 minutes
                        $.get("handler.py", query)
                            .done(function(response) {})
                            .always(handle_response);
                    }

                    function check(query) {
                        var fail = false;

                        if (full_inp) {
                            var path = document.getElementById("path").value;
                            var ref_path = document.getElementById("ref_path").value;
                            if (path == "" || ref_path == "")   {
                                fail = true;
                            }
                        }
                        else {
                            var ref_sample = document.getElementById("ref_sample").value;
                            var ref_patch = document.getElementById("ref_patch").value;
                            var ref_vers = document.getElementById("ref_vers").value;
                            if (ref_sample == "" || sample == "" || ref_patch == "" || patch == "" || ref_vers == "" || vers == "") {
                                fail = true;
                            }
                        }

                        if (fail) {
                            $("#input_err").show();
                        }

                        else {
                            $("#input_err").hide();
                            console.log(query);
                            submit(query);
                        }
                    }
                    // End query handlers

                    // Main function
                    $(function() {
                        // Initital hides
                        $("#load").hide();
                        $("#finished").hide();
                        $("#input_err").hide();
                        $("#internal_err").hide();

                        // Prevent 'enter' key from submitting forms (gives 404 error with full data set name form)
                        $(window).keydown(function(event) {
                            if (event.keyCode == 13) {
                                event.preventDefault();
                                return false;
                            }
                        });

                        // Main query handler
                        $("#submit").click(function(){
                            var query = {
                                "data_query": $("#preview").text(),
                                "ref_query": $("#ref_preview").text(),
                            }
                            check(query);
                        });
                    
                    });

                </script>

        </head>

        <body>
            <ul class="nav nav-tabs" id="navbar" role="tablist">
                <li role="presentation" class="active"><a href="./">AutoDQM</a></li>
                <li role="presentation"><a href="./">Search</a></li>
                <li role="presentation"><a href="plots.php">Plots</a></li>
            </ul>


            <div class="container-wide">
                <div class="row">
                    <div class="col-lg-6">
                        <h2>Data</h2>
                        <hr>
                        <div class="row">
                            <form id="modular" action="/" method="post" role="form">
                                <div class="form-group row">
                                    <div class="col-sm-8">
                                        <label for="sample">Sample</label>
                                        <input type="text" class="form-control" id="sample" onkeyup="updt_data()" name="sample" placeholder="e.g. RelValZMM_14">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-sm-8">
                                        <label for="patch">CMSSW Version</label>
                                        <input type="text" class="form-control" id="patch" onkeyup="updt_data()" name="patch" placeholder="e.g. 9_1_1_patch1-91X">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-sm-8">
                                        <label for="vers">Processing Version</label>
                                        <input type="text" class="form-control" id="vers" onkeyup="updt_data()" name="vers" placeholder="e.g. v1_D17-v1">
                                    </div>
                                </div>
                            </form>
                        </div> <!-- end row 1 -->

                        <hr>

                        <div class="row">
                            <div class="col-md-10">
                                <form id="full" action="./" method="post" role="form">
                                    <div class="form-group row">
                                        <label for="path">Dataset Name</label>
                                        <input type="text" class="form-control" id="path" onkeyup="dataName_full()" name="path" placeholder="e.g. /RelValZMM_14/CMSSW_9_1_1_patch1-91X_upgrade2023_realistic_v1_D17-v1/DQMIO">
                                    </div>
                                </form>
                            </div>
                        </div>

                    </div> <!-- end left col -->

                    <div class="col-lg-6">
                        <h2>Reference</h2>
                        <hr>
                        <div class="row">
                            <form id="ref_modular" action="/" method="post" role="form">
                                <div class="form-group row">
                                    <div class="col-sm-8">
                                        <label for="ref_sample">Sample</label>
                                        <input type="text" class="form-control" id="ref_sample" onkeyup="updt_ref()" name="ref_sample" placeholder="e.g. RelValZMM_14">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-sm-8">
                                        <label for="ref_patch">CMSSW Version</label>
                                        <input type="text" class="form-control" id="ref_patch" onkeyup="updt_ref()" name="ref_patch" placeholder="e.g. 9_1_1_patch1-91X">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-sm-8">
                                        <label for="ref_vers">Processing Version</label>
                                        <input type="text" class="form-control" id="ref_vers" onkeyup="updt_ref()" name="ref_vers" placeholder="e.g. v1_D17-v1">
                                    </div>
                                </div>
                            </form>
                        </div> <!-- end row 1 -->

                        <hr>

                        <div class="row">
                            <div class="col-md-10">
                                <form id="ref_full" action="./" method="post" role="form">
                                    <div class="form-group row">
                                        <label for="ref_path">Dataset Name</label>
                                        <input type="text" class="form-control" id="ref_path" onkeyup="refName_full()" name="ref_path" placeholder="e.g. /RelValZMM_14/CMSSW_9_1_1_patch1-91X_upgrade2023_realistic_v1_D17-v1/DQMIO">
                                    </div>
                                </form>
                            </div>
                        </div>

                    </div > <!-- end right col -->

                </div> <!-- end main row -->

                <p><br /><br /></p>

                <div class="row">
                    <div class="col-lg-3">
                    </div> <!-- end secondary row left padding -->
                    <div class="col-lg-6">
                        <div class="row">
                            <div class="col-md-12">
                                <label for="preview_well">Data Preview</label>
                                <div class="alert alert-success" id="preview_well">
                                    <p id="preview">/RelValZMM_14/CMSSW_9_1_1_patch1-91X_upgrade2023_realistic_v1_D17-v1/DQMIO</p>
                                </div>
                            </div>
                        </div> <!-- end preview row -->
                        <div class="row">
                            <div class="col-md-12">
                                <label for="ref_preview_well">Reference Preview</label>
                                <div class="alert alert-info" id="ref_preview_well">
                                    <p id="ref_preview">/RelValZMM_14/CMSSW_9_1_1_patch1-91X_upgrade2023_realistic_v1_D17-v1/DQMIO</p>
                                </div>
                            </div>
                        </div> <!-- end ref_preview row -->
                        <div class="text-center">
                            <button id="submit" type="submit" class="btn btn-lg btn-success">Submit</button>
                            <p><br /></p>
                            <a href="plots.php"><div class="alert alert-success" id="finished">Success! Please navigate to the 'Plots' page to view the results.</div></a>
                            <div class="alert alert-danger" id="input_err">Error: Incomplete input.</div>
                            <div class="alert alert-danger" id="internal_err">Error: Internal error.</div>
                        </div>
                        <div class="loader" id="load"></div>
                    </div> <!-- end secondary row middle col -->
                    <div class="col-lg-3">
                    </div> <!-- end secondary row right padding -->
                </div> <!-- end secondary row -->

            </div> <!-- end container -->
        </body>
</html>
