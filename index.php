<?php
include('config.php');
include('session.php');
require_once __DIR__ . '/vendor/autoload.php';
$mpdf = new \Mpdf\Mpdf(['orientation' => 'L']);


$data = array();


if (isset($_POST['summary'])) {
    $query = "select distinct type from (select Type, Model, count(*) from inventory group by Type, Model) result";
    $statement = $conn->prepare($query);
    $statement->execute();


    while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
        array_push($data, $row['type']);
    }

    $query = "select Type, Model, count(*) as num from inventory where Type=? group by Type, Model ";
    $html  = '';
    $html .= '<head>';
    $html .= '<link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet">';
    $html .= '</head>';

    $html .= '<table class="table table-bordered table-striped" style="white-space:nowrap;width:100%;">';
    foreach ($data as $type) {
        $statement = $conn->prepare($query);
        $statement->bindParam(1, $type);
        $statement->execute();
        $total = 0;
        $html .= '<tr>';
        $html .= '<th>' . $type . '</th>';
        $html .= '</tr>';
        while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
            $html .= '<tr>';
            $html .= '<td></td>';
            $html .= '<td>' . $row['Model'] . '</td>';
            $html .= '<td>' . $row['num'] . '</td>';
            $html .= '</tr>';
            $total += $row['num'];
        }
        $html .= '<tr><th>Total:</th><td></td><td>' . $total . '</td></tr>';
    }

    $html .= '</table>';
    $mpdf->autoLangToFont = true;
    $mpdf->autoScriptToLang = true;
    $mpdf->WriteHTML($html);
    $mpdf->Output('summary.pdf', 'D');
} else if (isset($_POST['userReport'])) {
    $division = $_POST['owner_division'];
    $post = $_POST['owner_post'];
    $name = $_POST['owner_name'];
    $equipment_Type = $_POST['equipment_Type'];
    $equipment_Model = $_POST['equipment_Model'];
    $equipment_Serial = $_POST['equipment_Serial'];

    $query =  " SELECT i.Items_ID, o.owner_post, o.owner_name, o.owner_division, i.Type, i.Model, i.Serial, i.QR_Code FROM inventory i , owners o
    WHERE i.owner_id = o.owner_id ";

    if ($division != '') {
        $query = $query . " and o.owner_division like '%" . $division . "%'";
    }

    if ($post != '') {
        $query = $query . " and o.owner_post like '%" . $post . "%'";
    }

    if ($name != '') {
        $query = $query . " and o.owner_name like '%" . $name . "%'";
    }

    if ($equipment_Type != '') {
        $query = $query . " and i.Type like '%" . $equipment_Type . "%'";
    }

    if ($equipment_Model != '') {
        $query = $query . " and i.Model like '%" . $equipment_Model . "%'";
    }

    if ($equipment_Serial != '') {
        $query = $query . " and i.Serial like '%" . $equipment_Serial . "%'";
    }

    $statement = $conn->prepare($query);
    $statement->execute();
    $html  = '';
    $html .= '<head>';
    $html .= '<link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet">';
    $html .= '</head>';

    $html .= '<table class="table table-bordered table-striped" style="white-space:nowrap;width:100%;">';
    $html .= '<tr>';
    $html .= '<th>owner_division</th>';
    $html .= '<th>owner_post</th>';
    $html .= '<th>owner_name</th>';
    $html .= '<th>Type</th>';
    $html .= '<th>Model</th>';
    $html .= '<th>Serial</th>';
    $html .= '</tr>';
    while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
        $html .= '<tr>';
        $html .= '<td>' . $row['owner_division'] . '</td>';
        $html .= '<td>' . $row['owner_post'] . '</td>';
        $html .= '<td>' . $row['owner_name'] . '</td>';
        $html .= '<td>' . $row['Type'] . '</td>';
        $html .= '<td>' . $row['Model'] . '</td>';
        $html .= '<td>' . $row['Serial'] . '</td>';
        $html .= '</tr>';
    }
    $html .= '</table>';
    $mpdf->autoLangToFont = true;
    $mpdf->autoScriptToLang = true;
    $mpdf->WriteHTML($html);
    $mpdf->Output('userReport.pdf', 'D');
}

?>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>OverView</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/vue/dist/vue.js"></script>
    <script src="https://unpkg.com/axios/dist/axios.min.js"></script>
    <style>
        .modal-mask {
            position: fixed;
            z-index: 9998;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, .5);
            display: table;
            transition: opacity .3s ease;
        }

        .modal-wrapper {
            display: table-cell;
            vertical-align: middle;
        }

        #scrollbox {
            overflow-y: auto;
            max-height: calc(100vh - 150px);
        }

        #Files {
            white-space: normal;
            word-break: break-word;
            max-width: 100%;
        }
    </style>
</head>

<body>
    <div id="App">
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
        <div class="container col-md-3">
            <h2>Searching Form</h2>
            <form action="" method="post">
                <div class="form-group">
                    <label for="owner_division">owner_division:</label>
                    <input type="text" class="form-control input-sm" ref="owner_division" name="owner_division" autocomplete="off" />
                </div>
                <div class="form-group">
                    <label for="owner_post">owner_post:</label>
                    <input type="text" class="form-control input-sm" ref="owner_post" name='owner_post' autocomplete="off" />
                </div>
                <div class="form-group">
                    <label for="owner_name">owner_name:</label>
                    <input type="text" class="form-control input-sm" ref="owner_name" name="owner_name" autocomplete="off" />
                </div>
                <div class="form-group">
                    <label for="equipment_Type">Type:</label>
                    <input type="text" class="form-control input-sm" ref="equipment_Type" name="equipment_Type" autocomplete="off" />
                </div>
                <div class="form-group">
                    <label for="equipment_Model">Model:</label>
                    <input type="text" class="form-control input-sm" ref="equipment_Model" name="equipment_Model" autocomplete="off" />
                </div>
                <div class="form-group">
                    <label for="equipment_Serial">Serial:</label>
                    <input type="text" class="form-control input-sm" ref="equipment_Serial" name="equipment_Serial" autocomplete="off" />
                </div>
                <div class="form-group">
                    <button type="button" class="btn btn-success" @click="Seraching">submit Data</button>
                    <button type="submit" class="btn btn-success" name="summary">Generate summary</button>
                    <button type="submit" class="btn btn-success" name="userReport">Generate User Report</button>
                </div>
            </form>
        </div>
        <div class="container col-md-8">
            <br />
            <h3 align="center">Audit Commission Inventory</h3>
            <br />
            <div class="panel panel-default">
                <div class="panel-heading">
                    <div class="row">
                        <div class="col-md-6">
                            <h3 class="panel-title">Sample Data</h3>
                        </div>
                        <div class="col-md-6" align="right">
                            <input type="button" class="btn btn-success btn-xs" @click="ModifyGroup" value="Modify group" />
                            <input type="button" class="btn btn-success btn-xs" @click="openModel" value="Add" />
                        </div>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="table" style="white-space:nowrap;width:100%;" ref="table">
                            <tr>
                                <th>owner_name</th>
                                <th>owner_post</th>
                                <th>owner_division</th>
                                <th>Type</th>
                                <th>Model</th>
                                <th>Serial</th>
                                <th>QR_Code</th>
                                <th>File_reference</th>
                                <th>Maintaince</th>
                                <th>operation</th>
                            </tr>
                            <tr v-for="row in allData">
                                <td>{{ row.owner_name}}</td>
                                <td>{{ row.owner_post}}</td>
                                <td>{{row.owner_division}}</td>
                                <td>{{row.Type}}</td>
                                <td>{{row.Model}}</td>
                                <td>{{row.Serial}}</td>
                                <td><a :href="'data:image/png;base64,'+row.QR_Code" :download='row.Serial'><img :src="'data:image/png;base64,'+row.QR_Code" width="75px" height="75px"></img></a></td>
                                <td id="Files">{{row.File_reference}}</td>
                                <td id="Files">{{row.Maintaince}}</td>
                                <td><button type="button" name="edit" class="btn btn-primary btn-xs edit" @click="fetchData(row.Items_ID)">Edit</button>
                                    <button type="button" name="delete" class="btn btn-danger btn-xs delete" @click="deleteData(row.Items_ID)">Delete</button>
                                </td>
                            </tr>
                        </table>

                    </div>
                </div>
            </div>
            <div v-if="myModel">
                <transition name="model">
                    <div class="modal-mask">
                        <div class="modal-wrapper">
                            <div class="modal-dialog modal-dialog-scrollable">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <button type="button" class="close" @click="myModel=false"><span aria-hidden="true">&times;</span></button>
                                        <h4 class="modal-title">{{ dynamicTitle }}</h4>
                                    </div>
                                    <div class="modal-body" id="scrollbox">
                                        <div class="form-group">
                                            <label for="division">Division</label>
                                            <select class="form-control" id="division" ref="division" @change="changepost" v-model="targetDivision">
                                                <option v-for="division in alldivision" v-bind:value="division" :selected="division == targetDivision">{{division}}</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="post">Post</label>
                                            <select class="form-control" id="post" ref="post" v-model="targetPost" @change="changename">
                                                <option v-for="post in allpost" v-bind:value="post" :selected="post == targetPost">{{post}}</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="name">Name</label>
                                            <input type="text" class="form-control" v-model="name" ref="name" disabled />
                                        </div>
                                        <div class="form-group">
                                            <label for="Type">Type</label>
                                            <select class="form-control" id="Type" ref="Type" v-model="targetType" @change="initialModel">
                                                <option v-for="type in allType" v-bind:value="type" :selected="type == targetType">{{type}}</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="Model">Model</label>
                                            <select class="form-control" id="Model" ref="Model" v-model="targetModel">
                                                <option v-for="model in allModel" v-bind:value="model" :selected="model == targetModel">{{model}}</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="Serial">Serial</label>
                                            <input type="text" class="form-control" id="Serial" v-model="targetSerial" />
                                        </div>
                                        <div class="form-group">
                                            <label for="File_ref">File_reference</label>
                                            <textarea class="form-control" id="File_ref" v-model="targetFileRef"></textarea>
                                        </div>
                                        <div class="form-group">
                                            <label for="maintenance">Maintenance</label>
                                            <textarea class="form-control" id="maintenance" v-model="targetmaintenance"></textarea>
                                        </div>

                                        <div align="center">
                                            <input type="hidden" v-model="hiddenId">
                                            <input type="button" class="btn btn-success btn-xs" v-model="actionButton" @click="submitData" />
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </transition>
            </div>
            <div v-if="group">
                <transition name="model">
                    <div class="modal-mask">
                        <div class="modal-wrapper">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <button type="button" class="close" @click="group=false"><span aria-hidden="true">&times;</span></button>
                                        <h4 class="modal-title">{{ dynamicTitle }}</h4>
                                    </div>
                                    <div class="modal-body" id="scrollbox">
                                        <div class="form-group">
                                            <label for="Type">Type</label>
                                            <select class="form-control" id="Type" ref="Type" v-model="targetType" @change="initialModel">
                                                <option v-for="type in allType" v-bind:value="type" :selected="type == targetType">{{type}}</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="Model">Model</label>
                                            <select class="form-control" id="Model" ref="Model" v-model="targetModel">
                                                <option v-for="model in allModel" v-bind:value="model" :selected="model == targetModel">{{model}}</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <input type="checkbox" v-model="needUpdateFileRef" />
                                            <label>Update File Reference</label>
                                        </div>
                                        <div class="form-group">
                                            <label for="File_ref">File_reference</label>
                                            <textarea class="form-control" id="File_ref" v-model="targetFileRef" :disabled="!needUpdateFileRef"></textarea>
                                        </div>
                                        <div class="form-group">
                                            <input type="checkbox" v-model="needUpdateMaintenance" />
                                            <label>update maintenance</label>
                                        </div>
                                        <div class="form-group">
                                            <label for="maintenance">Maintenance</label>
                                            <textarea class="form-control" id="maintenance" v-model="targetmaintenance" :disabled="!needUpdateMaintenance"></textarea>
                                        </div>
                                        <div class="form-group">
                                            <input type="button" class="btn btn-primary btn-xs" v-model="actionButton" @click="submitData" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </transition>
            </div>
            <div class="container">
                <ul class="pagination">
                    <li class="page-item disabled"><button class="page-link" @click="previousPage" :disabled="isFirstPage">Previous</button></li>
                    <li class="page-item"><input type="text" class="form-control-inline" style="width:40px;" v-model="currentPage" disabled /></li>
                    <li class="page-item"><input type="text" class="form-control-inline" style="width:50px; " v-model="showPage" disabled /></li>
                    <li class="page-item"><button class="page-link" @click="nextPage" :disabled="isLastPage">Next</button></li>
                    <!--<li class="page-item"><button class="page-link" @click="GetAllId">update all</button>-->
                </ul>
            </div>
        </div>
    </div>
</body>

</html>

<script>
    var applications = new Vue({
        el: '#App',
        data: {
            allData: '',
            alldivision: '',
            allpost: '',
            allType: '',
            allModel: '',
            allSerial: '',
            allId: '',
            name: '',
            hiddenId: '',
            myModel: false,
            actionButton: 'Insert',
            dynamicTitle: 'Add Data',
            currentPage: 1,
            numberOfPage: 5,
            offset: 5,
            allDataCount: 0,
            Search_division: '',
            Search_post: '',
            Search_name: '',
            Search_type: '',
            Search_Model: '',
            Search_Serial: '',
            showPage: '',
            summaryType: '',
            summaryData: '',
            userReport: '',
            group: false,
            needUpdateFileRef: false,
            needUpdateMaintenance: false,
            login_session: <?php echo $login_session; ?>,
            username: <?php echo json_encode($user_name); ?>,
        },
        methods: {
            fetchAllData: async function() {
                await axios.post('action.php', {
                    action: 'fetchall',
                    currentPage: this.currentPage,
                    offset: this.numberOfPage,
                    Search_division: this.Search_division,
                    Search_post: this.Search_post,
                    Search_name: this.Search_name,
                    Search_Type: this.Search_type,
                    Search_Model: this.Search_Model,
                    Search_Serial: this.Search_Serial
                }).then(function(response) {
                    console.log(response.data);
                    applications.allData = response.data;
                });

                await applications.SearchingCounter();
                console.log(applications.allDataCount);
                applications.totalPage = Math.ceil(applications.allDataCount / applications.numberOfPage);
                applications.showPage = "/" + applications.totalPage;
            },
            openModel: async function() {
                this.hiddenId = '';
                this.targetDivision = '';
                this.targetPost = '';
                this.name = '';
                this.targetType = '';
                this.targetModel = '';
                this.targetSerial = '';
                this.targetFileRef = '';
                this.targetmaintenance = '';
                this.actionButton = "Insert";
                this.dynamicTitle = "Add Data";
                this.myModel = true;
                await this.initialDivision();
                await this.initialEquipment();
            },
            fetchData: async function(id) {
                await axios.post('action.php', {
                    action: 'fetchSingle',
                    id: id
                }).then(function(response) {
                    console.log(response);
                    applications.hiddenId = response.data.Items_ID;
                    applications.targetDivision = response.data.owner_division;
                    applications.targetPost = response.data.owner_post;
                    applications.name = response.data.owner_name;
                    applications.targetType = response.data.Type;
                    applications.targetModel = response.data.Model;
                    applications.targetSerial = response.data.Serial;
                    applications.targetFileRef = response.data.File_reference;
                    applications.targetmaintenance = response.data.Maintaince;
                    applications.myModel = true;
                    applications.actionButton = 'update';
                    applications.dynamicTitle = 'Edit Data';
                    applications.initialDivision();
                    applications.initialEquipment();
                });
            },
            submitData: async function() {

                if (applications.actionButton == 'Insert') {
                    await applications.getOnwerID();
                    console.log(applications.owner_ID);

                    await axios.post('action.php', {
                        action: 'insert',
                        owner_ID: this.owner_ID,
                        Type: this.targetType,
                        Model: this.targetModel,
                        Serial: this.targetSerial,
                        FileRef: this.targetFileRef,
                        maintenance: this.targetmaintenance
                    }).then(function(response) {
                        console.log(response);
                        alert(response.data.message);
                        applications.myModel = false;
                        applications.SearchingCounter();
                        applications.fetchAllData();

                    });

                } else if (applications.actionButton == 'update') {

                    await applications.getOnwerID();

                    await axios.post('action.php', {
                        action: 'update',
                        owner_ID: this.owner_ID,
                        Items_ID: this.hiddenId,
                        Type: this.targetType,
                        Model: this.targetModel,
                        Serial: this.targetSerial,
                        FileRef: this.targetFileRef,
                        maintenance: this.targetmaintenance
                    }).then(function(response) {
                        console.log(response);
                        applications.myModel = false;
                        applications.fetchAllData();
                        applications.hiddenId = '';
                        applications.targetDivision = '';
                        applications.targetPost = '';
                        applications.name = '';
                        applications.targetType = '';
                        applications.targetModel = '';
                        applications.targetSerial = '';
                        alert(response.data.message);
                    });
                } else if (this.actionButton == 'submit result') {
                    console.log(this.needUpdateFileRef);
                    console.log(this.needUpdateMaintenance);

                    if (this.needUpdateFileRef) {
                        await axios.post('action.php', {
                            action: 'groupEditRef',
                            Type: this.targetType,
                            Model: this.targetModel,
                            FileRef: this.targetFileRef,
                            //needUpdateFileRef: this.needUpdateFileRef,
                            //needUpdateMaintenance: this.needUpdateMaintenance
                        }).then(function(response) {
                            console.log(response);
                            applications.group = false;
                            //alert(response.data.message);
                            applications.fetchAllData();
                            applications.needUpdateFileRef = false;
                        });
                    }

                    if (this.needUpdateMaintenance) {
                        await axios.post('action.php', {
                            action: 'groupEditMaintenance',
                            Type: this.targetType,
                            Model: this.targetModel,
                            maintenance: this.targetmaintenance,
                        }).then(function(response) {
                            console.log(response);
                            applications.group = false;
                            applications.fetchAllData();
                            applications.needUpdateMaintenance = false;
                        })
                    }
                }

            },
            deleteData: async function(id) {
                if (confirm("Are you sure you want to remove this data?")) {
                    await axios.post('action.php', {
                        action: 'delete',
                        id: id
                    }).then(function(response) {
                        applications.getAllDataCount();
                        applications.fetchAllData();
                        if (applications.allData.length == 1) {
                            if (applications.currentPage > 1) applications.currentPage--;
                            applications.fetchAllData();
                        }
                        alert(response.data.message);
                    });
                }
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

            initialDivision: async function() {
                await axios.post('action.php', {
                    action: 'initial'
                }).then(function(response) {
                    console.log(response);
                    //console.log(applications.actionButton);
                    console.log(applications.$refs.division.options);
                    console.log(applications.targetDivision);
                    if (applications.actionButton == 'Insert') {
                        applications.targetDivision = response.data[0];
                    }
                    applications.alldivision = response.data;
                    applications.initialPost(applications.targetDivision);
                });
            },

            initialPost: async function(division) {
                await axios.post('action.php', {
                    action: 'initialPost',
                    division: division
                }).then(function(response) {
                    console.log(response);
                    if (applications.actionButton == 'Insert') {
                        applications.targetPost = response.data[0];
                    }
                    applications.allpost = response.data;
                    applications.changename();
                });
            },

            changepost: async function() {
                await axios.post('action.php', {
                    action: 'initialPost',
                    division: this.targetDivision
                }).then(function(response) {
                    console.log(response);
                    var post = applications.$refs.post;
                    post.innerHTML = '';
                    console.log(post);
                    for (var i = 0; i < response.data.length; i++) {
                        var options = document.createElement('Option')
                        options.setAttribute('value', response.data[i]);
                        var textNode = document.createTextNode(response.data[i]);
                        options.appendChild(textNode);
                        post.appendChild(options);
                    }

                    applications.targetPost = post.value;
                    applications.changename();

                });

            },
            changename: async function() {
                await axios.post('action.php', {
                    action: 'changeName',
                    division: this.targetDivision,
                    post: this.targetPost
                }).then(function(response) {
                    console.log(response);
                    applications.name = response.data.owner_name;
                });
            },


            getEquipmentID: async function() {
                console.log(applications.targetSerial);
                await axios.post('action.php', {
                    action: 'getEquipmentID',
                    Type: this.targetType,
                    Model: this.targetModel,
                    Serial: this.targetSerial
                }).then(function(response) {
                    console.log(response);
                    applications.EquipmentID = response.data[0];
                });
            },

            getOnwerID: async function() {
                await axios.post('action.php', {
                    action: 'getOwnerID',
                    division: this.targetDivision,
                    post: this.targetPost,
                    name: this.name
                }).then(function(response) {
                    console.log(response);
                    applications.owner_ID = response.data[0];
                });
            },

            nextPage: function() {
                this.currentPage++;
                this.fetchAllData();
            },

            previousPage: function() {
                this.currentPage--;
                this.fetchAllData();
            },

            getAllDataCount: async function() {
                await axios.post('action.php', {
                    action: 'count'
                }).then(function(response) {
                    console.log(response.data);
                    applications.allDataCount = response.data;
                })
            },

            Seraching: async function() {
                this.Search_division = this.$refs.owner_division.value;
                this.Search_post = this.$refs.owner_post.value;
                this.Search_name = this.$refs.owner_name.value;
                this.Search_type = this.$refs.equipment_Type.value;
                this.Search_Model = this.$refs.equipment_Model.value;
                this.Search_Serial = this.$refs.equipment_Serial.value
                this.currentPage = 1;
                this.fetchAllData();
                //this.SearchingCounter();
            },

            SearchingCounter: async function() {
                await axios.post('action.php', {
                    action: 'SearchingCounter',
                    Search_division: this.Search_division,
                    Search_post: this.Search_post,
                    Search_name: this.Search_name,
                    Search_Type: this.Search_type,
                    Search_Model: this.Search_Model,
                    Search_Serial: this.Search_Serial
                }).then(function(response) {
                    console.log(response.data);
                    applications.allDataCount = response.data;
                })
            },

            GetAllId: async function() {
                await axios.post('action.php', {
                    action: 'allId'
                }).then(function(response) {
                    console.log(response);
                    applications.allId = response.data;
                });

                for (var i = 0; i < applications.allId.length; i++) {
                    this.update(applications.allId[i]);
                }

                alert('update all successful');
            },

            update: async function(id) {
                await axios.post('action.php', {
                    action: 'updateAll',
                    id: id
                }).then(function(response) {
                    console.log(response);
                });
            },

            ModifyGroup: async function() {
                this.group = true;
                this.dynamicTitle = "group edit";
                this.targetType = '';
                this.targetModel = '';
                this.targetFileRef = '';
                this.targetmaintenance = '';
                this.actionButton = "submit result";
                await this.initialEquipment();
            }

        },

        computed: {
            isFirstPage: function() {
                return (this.currentPage <= 1)
            },

            isLastPage: function() {
                return (this.currentPage >= Math.ceil(this.allDataCount / this.numberOfPage))
            }
        },


        created: async function() {
            await this.fetchAllData();
            //await this.SearchingCounter();
            //this.totalPage = Math.ceil(applications.allDataCount / applications.numberOfPage);
            //this.showPage = "/" + applications.totalPage;
        },

    });
</script>