<?php include('session.php'); ?>
<html">

   <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
      <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet">
      <script src="https://cdn.jsdelivr.net/npm/vue/dist/vue.js"></script>
      <script src="https://unpkg.com/axios/dist/axios.min.js"></script>
      <title>Welcome </title>
   </head>

   <body>
      <div id="app">
         <nav class="navbar navbar-inverse navbar-static-top">
            <div class="container-fluid">
               <div class="navbar-header">
                  <a class="navbar-brand" href="#">Audit commission Inventory</a>
               </div>
               <ul class="nav navbar-nav navbar-right">
                  <li>
                     <p class="navbar-text">welcome {{username}}</p>
                  </li>
                  <li><a href="logout.php">Sign Out</a></li>
               </ul>
            </div>
         </nav>
         <div>
            <input type="hidden" name="Items_Id" v-model="login_session" ref="Login" />
            <div class="container">
               <h3 align="center">Audit Commission Inventory</h3>
               <div class="panel panel-default">
                  <div class="panel-heading">
                     <div class="row">
                        <div class="col-md-6">
                           <h3 class="panel-title">Inventory Data</h3>
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
                           </tr>
                           <tr v-for="user in allItemByUser">
                              <td>{{ user.owner_name}}</td>
                              <td>{{ user.owner_post}}</td>
                              <td>{{user.owner_division}}</td>
                              <td>{{user.Type}}</td>
                              <td>{{user.Model}}</td>
                              <td>{{user.Serial}}</td>
                              <td><img :src="'data:image/png;base64,'+user.QR_Code" width="75px" height="75px"></img></td>
                           </tr>
                        </table>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </body>

   </html>

   <script>
      var applications = new Vue({
         el: '#app',
         data: {
            login_session: <?php echo $login_session; ?>,
            username: <?php echo json_encode($user_name);?>,
            allItemByUser: []
         },
         methods: {
            fetchByLoginSession: async function() {
               await axios.post('action.php', {
                  action: 'fetchByLoginSession',
                  owner_id: this.login_session
               }).then(function(response) {
                  console.log(response);
                  applications.allItemByUser = response.data;
               })
            }
         },
         created: async function() {
            console.log(this.login_session);
            this.fetchByLoginSession();
         },


      });
   </script>