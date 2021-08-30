<?php include('session.php') ?>
<html>

<head>
    <title>User</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta charset="UTF-8">
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/vue/dist/vue.js"></script>
    <script src="https://unpkg.com/axios/dist/axios.min.js"></script>
</head>
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
            <div>
                <label for="searchname">owner_name:</label>
                <input type="text" name="searchname" ref="searchname" class="form-control" v-model="searchName" @keyup="search()" />
            </div>

            <div>
                <label for="searchpost">owner_post:</label>
                <input type="text" name="searchpost" ref="searchpost" class="form-control" v-model="searchPost" @keyup="search()" />
            </div>

            <div>
                <label for="searchdivision">owner_division:</label>
                <input type="text" name="searchdivsion" ref="searchdivsion" class="form-control" v-model="searchDivision" @keyup="search()" />
            </div>
        </div>
        <br />
        <div class="container">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <div class="row">
                        <div class="col-md-6">
                            <h3 class="panel-title">User</h3>
                        </div>

                        <div class="col-md-6" align="right">
                            <input type="button" class="btn btn-success btn-xs" @click="openModel" value="Add" />
                        </div>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <tr>
                                <th>owner_name</th>
                                <th>owner_post</th>
                                <th>owner_divsion</th>
                                <th>Operations</th>
                            </tr>
                            <tr v-for="user in allUser">
                                <td>{{user.owner_name}}</td>
                                <td>{{user.owner_post}}</td>
                                <td>{{user.owner_division}}</td>
                                <td><button type="button" name="edit" class="btn btn-primary btn-xs edit" @click="UpdateUser(user.owner_id)">Edit</button>
                                    <button type="button" name="delete" class="btn btn-danger btn-xs delete" @click="DeleteUser(user.owner_id)">Delete</button>
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
                                    <h4 class="modal-title">{{dynamicTitle}}</h4>
                                </div>
                                <div class="modal-body" id="scrollbox">
                                    <div class="form-group">
                                        <label for="division">owner_division:</label>
                                        <select class="form-control" ref="division" id="division" @change="checkOption">
                                            <option v-for="division in allDivision" v-bind:value="division">{{division}}</option>
                                        </select>
                                    </div>
                                    <div class="form-group" v-if="popOthers">
                                        <label for="others">Please specify:</label>
                                        <input type="text" name="others" ref="others" class="form-control" autocomplete="off" />
                                    </div>
                                    <div class="form-group">
                                        <label for="post">owner_post:</label>
                                        <input type="text" name="post" ref="post" class="form-control" autocomplete="off" />
                                    </div>
                                    <div class="form-group">
                                        <label for="name">owner_name</label>
                                        <input type="text" name="name" ref="name" class="form-control" autocomplete="off" />
                                    </div>
                                    <div class="form-group" align="center">
                                        <input type="hidden" v-model="hiddenId" />
                                        <input type="button" v-model="Operation" @click="submitData" class="btn btn-success btn-xs" />
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
                <li class="page-item"><input type="number" class="form-control-inline" style="width:40px;" v-model="currentPage" @change="PageNumber" /></li>
                <li class="page-item"><input type="text" class="form-control-inline" style="width:50px;" v-model="showpage" disabled /></li>
                <li class="page-item"><button class="page-link" @click="nextPage" :disabled="isLastPage">Next</button></li>
                <!--<li class="page-item"><button class="page-link" @click="GetAllId">update all</button>-->
            </ul>
        </div>
    </div>

</body>

</html>
<script>
    var applications = new Vue({
        el: '#app',
        data: {
            allUser: '',
            myModel: false,
            dynamicTitle: '',
            allDivision: '',
            popOthers: false,
            Operation: '',
            hiddenId: '',
            currentPage: 1,
            offset: 5,
            totalRecord: 0,
            showpage: '',
            searchName: '',
            searchPost: '',
            searchDivision: '',
            login_session: <?php echo $login_session; ?>,
            username: <?php echo json_encode($user_name); ?>,
        },
        methods: {
            fetchAllUser: async function() {
                await axios.post('action.php', {
                    action: 'FetchAllUser',
                    currentPage: this.currentPage,
                    offset: this.offset,
                    name: this.searchName,
                    post: this.searchPost,
                    division: this.searchDivision
                }).then(function(response) {
                    console.log(response);
                    applications.allUser = response.data;
                });

                await this.fetchAllUserCount();
            },

            openModel: async function() {
                this.myModel = true;
                this.dynamicTitle = 'add User';
                this.Operation = 'add';
                await this.fetchOwnerDivision();
            },

            fetchOwnerDivision: async function() {
                await axios.post('action.php', {
                    action: 'initial'
                }).then(function(response) {
                    console.log(response);
                    applications.allDivision = response.data;
                    applications.allDivision.push("Others");
                });
            },

            checkOption: function() {
                var options = this.$refs.division.value;

                if (options == 'Others') {
                    this.popOthers = true;
                } else {
                    this.popOthers = false;
                }
            },

            submitData: async function() {

                if (this.Operation == 'add') {

                    if (this.popOthers) {
                        var others = this.$refs.others.value;
                        var post = this.$refs.post.value;
                        var name = this.$refs.name.value;

                        if (others != '' && post != '' && name != '') {
                            await axios.post('action.php', {
                                action: 'addUser',
                                division: others,
                                post: post,
                                name: name
                            }).then(function(response) {
                                console.log(response);
                                alert(response.data.message);
                                applications.myModel = false;
                                applications.popOthers = false;
                                applications.fetchAllUser();
                            });
                        } else {
                            alert('must fill up all field');
                        }
                    } else {
                        var division = this.$refs.division.value;
                        var post = this.$refs.post.value;
                        var name = this.$refs.name.value;

                        if (division != '' && post != '' && name != '') {
                            await axios.post('action.php', {
                                action: 'addUser',
                                division: division,
                                post: post,
                                name: name
                            }).then(function(response) {
                                console.log(response);
                                alert(response.data.message);
                                applications.myModel = false;
                                applications.popOthers = false;
                                applications.fetchAllUser();
                            });
                        }
                    }

                } else if (this.Operation == 'Update') {
                    if (this.popOthers) {
                        var others = this.$refs.others.value;
                        var post = this.$refs.post.value;
                        var name = this.$refs.name.value;

                        if (others != '' && post != '' && name != '') {
                            await axios.post('action.php', {
                                action: 'updateUser',
                                division: others,
                                post: post,
                                name: name,
                                id: this.hiddenId
                            }).then(function(response) {
                                console.log(response);
                                alert(response.data.message);
                                applications.myModel = false;
                                applications.popOthers = false;
                                applications.fetchAllUser();
                            });
                        }
                    } else {
                        var division = this.$refs.division.value;
                        var post = this.$refs.post.value;
                        var name = this.$refs.name.value;

                        if (division != '' && post != '' && name != '') {
                            await axios.post('action.php', {
                                action: 'updateUser',
                                division: division,
                                post: post,
                                name: name,
                                id: this.hiddenId
                            }).then(function(response) {
                                console.log(response);
                                alert(response.data.message);
                                applications.myModel = false;
                                applications.popOthers = false;
                                applications.fetchAllUser();
                            });
                        }
                    }
                }

            },

            DeleteUser: async function(id) {
                await axios.post('action.php', {
                    action: 'DeleteUser',
                    id: id
                }).then(function(response) {
                    console.log(response);
                    alert(response.data.message);
                    applications.fetchAllUser();
                });
            },

            UpdateUser: async function(id) {
                this.myModel = true;
                this.dynamicTitle = 'Update User';
                this.Operation = 'Update';
                await this.fetchOwnerDivision();

                await axios.post('action.php', {
                    action: 'fetchUser',
                    id: id
                }).then(function(response) {
                    console.log(response);
                    applications.targetUser = response.data[0];
                    applications.hiddenId = parseInt(applications.targetUser.owner_id);
                });

                var division = this.$refs.division;
                division.value = this.targetUser.owner_division;

                var post = this.$refs.post;
                post.value = this.targetUser.owner_post;

                var name = this.$refs.name;
                name.value = this.targetUser.owner_name;

            },

            fetchAllUserCount: async function() {
                await axios.post('action.php', {
                    action: 'fetchAllUserCount',
                    name: this.searchName,
                    post: this.searchPost,
                    division: this.searchDivision
                }).then(function(response) {
                    console.log(response);
                    applications.totalRecord = parseInt(response.data);
                    applications.showpage = "/" + Math.ceil(applications.totalRecord / applications.offset);
                });
            },

            nextPage: function() {
                this.currentPage++;
                this.fetchAllUser();
            },

            previousPage: function() {
                this.currentPage--;
                this.fetchAllUser();
            },

            search: function() {
                this.currentPage = 1;
                this.fetchAllUser();
            },

            PageNumber: function() {
                if (this.isLastPage) {
                    this.currentPage = Math.ceil(this.totalRecord / this.offset);
                }else if(this.isFirstPage){
                    this.currentPage = 1;
                }

                this.fetchAllUser();
            }


        },
        computed: {
            isFirstPage: function() {
                return this.currentPage <= 1;
            },

            isLastPage: function() {
                return this.currentPage >= Math.ceil(this.totalRecord / this.offset);
            }
        },
        created: async function() {
            await this.fetchAllUser();
        }
    });
</script>