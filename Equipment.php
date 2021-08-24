<?php
include('session.php');
?>
<html>

<head>
    <title>Equipment</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
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
    </style>
</head>

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

        <div class="container">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <div class="row">
                        <div class="col-md-3">
                            <h3 class="panel-title">Equipment</h3>
                        </div>
                        <div class="col-md-3">
                            <label>Type</label>
                            <input type="text" v-model="SearchType" class="form-control" @keyup="search()" />
                        </div>
                        <div class="col-md-3">
                            <label>Model</label>
                            <input type="text" v-model="SearchModel" class="form-control" @keyup="search()" />
                        </div>
                        <div class="col-md-3" align="right">
                            <input type="button" class="btn btn-success btn-xs" @click="openModel" value="Add" />
                        </div>
                    </div>

                </div>
                <div class="panel-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <tr>
                                <th>Type</th>
                                <th>Model</th>
                                <th>Operations</th>
                            </tr>

                            <tr v-for="equipment in AllEquipment">
                                <td>{{equipment.Type}}</td>
                                <td>{{equipment.Model}}</td>
                                <td><button type="button" name="edit" class="btn btn-primary btn-xs edit" @click="update(equipment.E_id)">Edit</button>
                                    <button type="button" name="delete" class="btn btn-danger btn-xs delete" @click="deleteData(equipment.E_id)">Delete</button>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div v-if="myModel">
            <transition name="model">
                <div class="modal-mask">
                    <div class="modal-wrapper">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <button type="button" class="close" @click="myModel=false"><span aria-hidden="true">&times;</span></button>
                                    <h4 class="modal-title">{{ dynamicTitle }}</h4>
                                </div>
                                <div class="modal-body" id="scrollbox">
                                    <div class="form-group">
                                        <label for="Type">Type</label>
                                        <select class="form-control" id="Type" ref="Type" @change="checkTypeOption">
                                            <option v-for="type in AllType" v-bind:value="type">{{type}}</option>
                                        </select>
                                    </div>
                                    <div class="form-group" v-if="popOthers">
                                        <label for="Others">Please specifiy:</label>
                                        <input type="text" id="Others" ref="Others" class="form-control" autocomplete="off" />
                                    </div>
                                    <div class="form-group">
                                        <label for="Model">Model:</label>
                                        <input type="text" class="form-control" ref="Model" id="Model" autocomplete="off" />
                                    </div>
                                    <div align="center">
                                        <input type="hidden" v-model="hiddenId" />
                                        <input type="button" class="btn btn-success btn-xs" v-model="Operation" @click="submitData"></Button>
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

</body>
<script>
    var applications = new Vue({
        el: '#app',
        data: {
            myModel: false,
            AllEquipment: '',
            dynamicTitle: '',
            Operation: '',
            AllType: '',
            popOthers: false,
            hiddenId: '',
            targetEquipment: '',
            currentPage: 1,
            offset: 5,
            numOfRecord: 0,
            showPage: '',
            SearchType: '',
            SearchModel: '',
            login_session: <?php echo $login_session; ?>,
            username: <?php echo json_encode($user_name); ?>,
        },
        methods: {
            fetchEquipment: async function() {
                await axios.post('action.php', {
                    action: 'AllEquipment',
                    currentPage: this.currentPage,
                    offset: this.offset,
                    Type: this.SearchType,
                    Model: this.SearchModel,
                }).then(function(response) {
                    console.log(response);
                    applications.AllEquipment = response.data;

                });

                await applications.fetchEquipmentCount();
            },


            openModel: async function() {
                this.myModel = true;
                this.dynamicTitle = 'Add Equipment';
                this.Operation = 'Add';
                await this.InitialEquipment();

            },

            InitialEquipment: async function() {
                await axios.post('action.php', {
                    action: 'initialEquipment'
                }).then(function(response) {
                    console.log(response);
                    applications.AllType = response.data;
                });
                this.AllType.push("Others");
            },

            checkTypeOption: function() {
                var option = this.$refs.Type.value;

                if (option == 'Others') {
                    this.popOthers = true;
                } else {
                    this.popOthers = false;
                }
            },

            submitData: async function() {
                if (this.Operation == 'Add') {
                    if (this.popOthers) {
                        var others = this.$refs.Others.value;
                        var Model = this.$refs.Model.value;

                        if (others != '' && Model != '') {
                            await axios.post('action.php', {
                                action: 'addEquipment',
                                Type: others,
                                Model: Model
                            }).then(function(response) {
                                console.log(response);
                                alert(response.data.message);
                                applications.myModel = false;
                                applications.fetchEquipment();
                                applications.popOthers = false;
                            });
                        } else {
                            alert('must fill up all field');
                        }
                    } else {
                        var Type = this.$refs.Type.value;
                        var Model = this.$refs.Model.value;

                        if (Type != '' && Model != '') {
                            await axios.post('action.php', {
                                action: 'addEquipment',
                                Type: Type,
                                Model: Model
                            }).then(function(response) {
                                console.log(response);
                                alert(response.data.message);
                                applications.myModel = false;
                                applications.fetchEquipment();
                                applications.popOthers = false;
                            });
                        } else {
                            alert('must fill up all field');
                        }


                    }
                } else if (this.Operation == 'Update') {
                    if (this.popOthers) {
                        var others = this.$refs.Others.value;
                        var Model = this.$refs.Model.value;

                        if (others != '' && Model != '') {
                            await axios.post('action.php', {
                                action: 'UpdateEquipment',
                                Type: others,
                                Model: Model,
                                id: this.hiddenId
                            }).then(function(response) {
                                console.log(response);
                                alert(response.data.message);
                                applications.myModel = false;
                                applications.fetchEquipment();
                                applications.popOthers = false;
                            });
                        } else {
                            alert('must fill up all field');
                        }
                    } else {
                        var Type = this.$refs.Type.value;
                        var Model = this.$refs.Model.value;

                        if (Type != '' && Model != '') {
                            await axios.post('action.php', {
                                action: 'UpdateEquipment',
                                Type: Type,
                                Model: Model,
                                id: this.hiddenId
                            }).then(function(response) {
                                console.log(response);
                                alert(response.data.message);
                                applications.myModel = false;
                                applications.fetchEquipment();
                                applications.popOthers = false;
                            });
                        }
                    }
                }
            },

            deleteData: async function(id) {
                if (confirm('Are you sure to delete this data?')) {
                    await axios.post('action.php', {
                        action: 'deleteEquipment',
                        id: id
                    }).then(function(response) {
                        console.log(response);

                        applications.fetchEquipment();
                        if (applications.AllEquipment.length == 1) {
                            if (applications.currentPage > 1) applications.currentPage--;
                            applications.fetchEquipment();
                        }

                        alert(response.data.message);
                    })
                }
            },

            update: async function(id) {
                await axios.post('action.php', {
                    action: 'fetchEquipment',
                    id: id
                }).then(function(response) {
                    console.log(response);
                    applications.myModel = true;
                    applications.Operation = 'Update';
                    applications.dynamicTitle = 'Update Equipment';
                    applications.targetEquipment = response.data;
                    applications.hiddenId = response.data[0].E_id;

                });
                await applications.InitialEquipment();
                var Type = this.$refs.Type;
                Type.value = applications.targetEquipment[0].Type;

                var Model = this.$refs.Model;
                Model.value = applications.targetEquipment[0].Model;

            },

            fetchEquipmentCount: async function() {
                await axios.post('action.php', {
                    action: 'fetchEquipmentCount',
                    Type: this.SearchType,
                    Model: this.SearchModel
                }).then(function(response) {
                    console.log(response);
                    applications.numOfRecord = parseInt(response.data);
                    applications.showPage = '/' + Math.ceil(applications.numOfRecord / applications.offset);
                });
            },

            search: async function() {
                this.currentPage = 1;
                await this.fetchEquipment();
            },

            previousPage: function() {
                this.currentPage--;
                this.fetchEquipment();
            },

            nextPage: function() {
                this.currentPage++;
                this.fetchEquipment();
            }


        },

        computed: {
            isFirstPage: function() {
                return this.currentPage <= 1;
            },

            isLastPage: function() {
                return this.currentPage >= Math.ceil(this.numOfRecord / this.offset);
            }
        },
        created: async function() {
            await this.fetchEquipment();
            //await this.fetchEquipmentCount();
        }

    });
</script>

</html>