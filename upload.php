<?php include('session.php'); ?>
<html>

<head>
    <title>Group update</title>
    <meta charset="UTF-8">
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <script src="https://unpkg.com/read-excel-file@4.x/bundle/read-excel-file.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/vue/dist/vue.js"></script>
    <script src="https://unpkg.com/axios/dist/axios.min.js"></script>
</head>
<style>
    .login-form {
        width: 340px;
        margin: 50px auto;
        font-size: 15px;
    }

    .login-form .login_border {
        margin-bottom: 15px;
        background: #f7f7f7;
        box-shadow: 0px 2px 2px rgba(0, 0, 0, 0.3);
        padding: 30px;
    }

    .form-control,
    .btn {
        min-height: 38px;
        border-radius: 2px;
    }
</style>

<body>
    <div id="app">
        <nav class="navbar navbar-inverse navbar-static-top">
            <div class="container-fluid">
                <div class="navbar-header">
                    <a class="navbar-brand" href="#">Audit commission Inventory</a>
                </div>
                <ul class="nav navbar-nav">
                    <li><a href="index.php">Overview</a></li>
                    <li><a href="Equipment.php">Equipment</a></li>
                    <li><a href="user.php">User</a></li>
                    <li><a href="upload.php">upload group</a></li>
                </ul>
                <ul class="nav navbar-nav navbar-right">
                    <li>
                        <p class="navbar-text">welcome {{username}}</p>
                    </li>
                    <li><a href="Logout.php">Sign out</a></li>
                </ul>
            </div>
        </nav>
        <div class="login-form" v-if="!upload">
            <div class="login_border">
                <div class="form-group">
                    <label for="Type">Type</label>
                    <select class="form-control" id="Type" ref="Type" name="Type" v-model="targetType" @change="initialModel">
                        <option v-for="type in allType" v-bind:value="type" :selected="type == targetType">{{type}}</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="Model">Model</label>
                    <select class="form-control" id="Model" name="Model" ref="Model" v-model="targetModel">
                        <option v-for="model in allModel" v-bind:value="model" :selected="model == targetModel">{{model}}</option>
                    </select>
                </div>
                <div class="form-group">
                    <button type="button" name="groupEdit" class="btn btn-primary" @click="ExportData">Export Data</button>
                    <button type="button" ref="preview" class="btn btn-primary" @click="previewData">Preview Data</button>
                </div>

            </div>
        </div>

        <div class="table-responsive" v-if="showPreviewData && !upload" id="datalist">
            <table class="table table-bordered table-striped" id="table" style="white-space:nowrap;width:100%;" ref="table">
                <tr>
                    <th>Items_ID</th>
                    <th>Type</th>
                    <th>Model</th>
                    <th>Serial</th>
                    <th>File_reference</th>
                    <th>Maintenance</th>
                </tr>
                <tbody>
                    <tr v-for="data in allData">
                        <td>{{data.Items_ID}}</td>
                        <td>{{data.Type}}</td>
                        <td>{{data.Model}}</td>
                        <td>{{data.Serial}}</td>
                        <td>{{data.File_reference}}</td>
                        <td>{{data.Maintaince}}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="login-form" v-if="upload">
            <div class="login_border">
                <div class="form-group">
                    <h5>Please submit your modify updated data (must be in .xlsx format)</h5>
                </div>
                <div class="form-group">
                    <input type="file" id="input" class="form-control" accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" />
                </div>
                <div class="form-group">
                    <button class="btn btn-primary" @click="GroupUpdate">submit update</button>
                    <button class="btn btn-primary" @click="ExitUpdate">Exit</button>
                </div>
            </div>
        </div>
    </div>
</body>
<script>
    var applications = new Vue({
        el: '#app',
        data: {
            upload: false,
            allType: '',
            allModel: '',
            actionButton: 'Insert',
            allData: '',
            showPreviewData: false,
            upload: false,
            targetType: '',
            targetModel: '',
            updateIDArray: [],
            login_session: <?php echo $login_session; ?>,
            username: <?php echo json_encode($user_name); ?>,
        },
        methods: {
            GroupUpdate: async function() {

                var input = document.getElementById("input");
                var filepath = input.value;
                var allowedExtension = /(\.xlsx)$/i;

                if (!allowedExtension.exec(filepath)) {
                    alert('Input the wrong type of file');
                } else {
                    var match = true;
                    await readXlsxFile(input.files[0]).then(function(data) {
                        console.log(data);
                        if (data.length != applications.updateIDArray.length) {
                            match = false;
                        } else {
                            for (var row = 0; row < data.length; row++) {
                                var Items_ID = data[row][0].toString();
                                if (!applications.updateIDArray.includes(Items_ID)) {
                                    match = false;
                                    break;
                                }
                            }
                        }
                    });

                    console.log(match);
                    if (match) {
                        var records = 0;
                        await readXlsxFile(input.files[0]).then(function(data) {
                            for (var row = 0; row < data.length; row++) {
                                if (data[row].length < 6) {
                                    continue;
                                }
                                var Items_ID = data[row][0];
                                var File_reference = data[row][4] == null ? "" : data[row][4];
                                var Maintaince = data[row][5] == null ? "" : data[row][5];
                                applications.IndividualUpdate(Items_ID, File_reference, Maintaince);
                                records++;
                            }
                        });

                        alert('update group successful ' + records + ' records');
                        window.location.href = "upload.php";
                    } else {
                        alert('upload failed: The file does not match the orignal export data')
                    }
                }
            },

            IndividualUpdate: async function(Items_ID, File_reference, Maintaince) {
                await axios.post('action.php', {
                    action: 'GroupUpdate',
                    Items_ID: Items_ID,
                    File_reference: File_reference,
                    Maintaince: Maintaince
                }).then(function(response) {
                    console.log(response.data.message);
                });
            },

            ExportData: async function() {

                await this.previewData();

                var csvList = [],
                    titleList = [],
                    memberContent = "",
                    csvContent;

                titleList.push('Items_ID', 'Type', 'Model', 'Serial', 'File_reference', 'Maintaince');
                //csvList.push(titleList);

                for (var i = 0; i < this.allData.length; i++) {
                    var regList = [];
                    for (var j = 0; j < titleList.length; j++) {
                        if (titleList[j] == 'Items_ID') {
                            this.updateIDArray.push(this.allData[i][titleList[j]]);
                        }
                        regList.push(this.allData[i][titleList[j]]);
                    }
                    csvList.push(regList);
                }

                csvList.forEach(function(rowArray) {
                    var row = rowArray.join(",");
                    memberContent += row + "\r\n";
                });
                //console.log(memberContent);


                csvContent = URL.createObjectURL(new Blob(["\uFEFF" + memberContent], {
                    type: 'text/csv;charset=utf-8;'
                }));

                var href = csvContent;

                var link = window.document.createElement('a');
                link.setAttribute('href', href);
                link.setAttribute('download', 'data.csv');
                link.style.display = 'none';
                window.document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);

                this.upload = true;
            },

            initialEquipment: async function() {
                await axios.post('action.php', {
                    action: 'initialEquipment'
                }).then(function(response) {
                    console.log(response);
                    if (applications.actionButton == 'Insert' || applications.actionButton == 'submit result') {
                        applications.targetType = response.data[0];
                    }
                    applications.allType = response.data;
                    applications.initialModel();
                });
            },

            initialModel: async function() {
                await axios.post('action.php', {
                    action: 'initialModel',
                    Type: this.targetType
                }).then(function(response) {
                    console.log(response);
                    if (applications.actionButton == 'Insert' || applications.actionButton == 'submit result') {
                        applications.targetModel = response.data[0];
                    }
                    applications.allModel = response.data;
                });
            },

            previewData: async function() {
                this.showPreviewData = true;
                await axios.post('action.php', {
                    action: 'previewData',
                    Type: this.targetType,
                    Model: this.targetModel
                }).then(function(response) {
                    console.log(response);
                    applications.allData = response.data;
                })
            },

            ExitUpdate: async function() {
                window.location.href = "index.php";
            }


        },
        mounted: async function() {
            await this.initialEquipment();
        },
    });
</script>

</html>