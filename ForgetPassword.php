<html>

<head>
    <title>Forget Password</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/vue/dist/vue.js"></script>
    <script src="https://unpkg.com/axios/dist/axios.min.js"></script>
    <style>
        .login-form {
            width: 400px;
            margin: 50px auto;
            font-size: 15px;
        }

        .login-background {
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



        .lds-dual-ring:after {
            content: " ";
            display: block;
            width: 64px;
            height: 64px;
            margin: 8px;
            border-radius: 50%;
            border: 6px solid #000;
            border-color: #000 transparent #000 transparent;
            animation: lds-dual-ring 1.2s linear infinite;
        }

        @keyframes lds-dual-ring {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }
    </style>
</head>

<body>
    <div id="app">
        <div class="login-form">
            <div class="login-background" v-if="RandomCode == false && isLoading == false">
                <h3 class="text-center">Forget password:</h3>
                <div class="form-group">
                    <input type="text" class="form-control" placeholder="Username" v-model="username" />
                </div>
                <div class="form-group">
                    <input type="password" class="form-control" placeholder="newpassword" v-model="newPassword" @keyup="ConfirmReq" @focus="showRequirement = true" />
                    <ul class="list-group" v-if="showRequirement">
                        <li class="list-group-item" ref="req1">new password should have at least 1 number</li>
                        <li class="list-group-item" ref="req2">new password should have at least 1 uppercase character</li>
                        <li class="list-group-item" ref="req3">new password should have at least 1 lowercase character</li>
                        <li class="list-group-item" ref="req4">new password should have length 8 or more</li>
                    </ul>
                </div>
                <div class="form-group">
                    <input type="password" class="form-control" placeholder="confirm password" v-model="confirmPassword" />
                </div>
                <div class="form-group">
                    <button class="btn btn-primary btn-block" @click="UpdateUserInfo">Submit</button>
                </div>
            </div>
            <div class="login-background" v-if="RandomCode && isLoading == false">
                <h3>Confirmation Code</h3>
                <div class="form-grop">
                    <p>Confirmation code email has been sent, please enter the confirmation code in email (6 characters):</p>
                    <input type="text" class="form-control" placeholder="Confirmation Code" ref="code" />
                </div>
                <div class="form-group">
                    <button class="btn btn-primary btn-block" @click="processUpdate">Confirm</button>
                </div>
                <div class="from-group">
                    <button class="btn btn-primary btn-block" @click="countdownTimer" :disabled="HaveTimer">{{countdownText}}</button>
                </div>
            </div>
        </div>
        <div align="center" class="lds-dual-ring" v-if="isLoading"></div>
    </div>

</body>

</html>
<script>
    var applications = new Vue({
        el: '#app',
        data: {
            username: '',
            confirmPassword: '',
            newPassword: '',
            showRequirement: false,
            RandomCode: false,
            confirmation_code: '',
            isLoading: false,
            sendEmail: false,
            time: 20,
            timer: null,
            countdownText: 'resend email'
        },
        methods: {
            UpdateUserInfo: async function() {

                if (this.username != '' && this.confirmPassword != '' && this.newPassword != '') {

                    await axios.post('action.php', {
                        action: 'UserExist',
                        username: this.username
                    }).then(function(response) {
                        console.log(response);
                        if (parseInt(response.data) == 1) {
                            applications.haveAccount = true;
                        }
                    });

                    if (this.haveAccount && this.sendEmail == false) {

                        var patt = new RegExp("(?=.*[0-9])(?=.*[a-z])(?=.*[A-Z]).{8,}");
                        var res = patt.test(this.newPassword);
                        if (res) {
                            if (this.newPassword == this.confirmPassword) {
                                this.isLoading = true;
                                await this.ConfirmCode();
                            } else {
                                alert('new password and confirm password not match or newpassword does not match requirement');
                            }
                        }
                    } else {
                        alert('Account not exists in the system');
                    }

                } else {
                    alert("Please fill up all column");
                }
            },

            ConfirmReq: function() {

                var req1 = this.$refs.req1;
                var req2 = this.$refs.req2;
                var req3 = this.$refs.req3;
                var req4 = this.$refs.req4;

                var pattern1 = new RegExp("(?=.*[0-9])");
                var pattern2 = new RegExp("(?=.*[A-Z])");
                var pattern3 = new RegExp("(?=.*[a-z])");
                var pattern4 = new RegExp(".{8,}");

                var req = [req1, req2, req3, req4];
                var pattern = [pattern1, pattern2, pattern3, pattern4];

                for (var i = 0; i < req.length; i++) {
                    if (pattern[i].test(this.newPassword)) {
                        req[i].classList.add('active');
                    } else {
                        if (req[i].classList.contains('active')) {
                            req[i].classList.remove('active');
                        }
                    }
                }
            },

            processUpdate: async function() {

                var userEnterCode = this.$refs.code.value;

                if (userEnterCode == this.confirmation_code) {

                    await axios.post('action.php', {
                        action: 'processUpdate',
                        username: this.username,
                        newpassword: this.newPassword
                    }).then(function(response) {
                        console.log(response);
                        alert(response.data.message);
                        window.location.href = "Login.php";
                    });
                } else {
                    alert('Confirmation Code Not match');
                }
            },

            ConfirmCode: async function() {
                var result = '';
                var characters = 'abcdefghijklmnopqrstuvwxyz0123456789'
                for (var i = 0; i < 6; i++) {
                    result += characters.charAt(Math.floor(Math.random() * characters.length));
                }
                this.confirmation_code = result;

                await axios.post('action.php', {
                    action: 'sendemail',
                    username: this.username,
                    code: this.confirmation_code
                }).then(function(response) {
                    console.log(response);
                    if (!applications.RandomCode) applications.RandomCode = true;
                    applications.isLoading = false;
                    applications.sendEmail = true;
                });
            },

            countdownTimer: function() {
                this.timer = setInterval(this.countdown, 1000);
                this.ConfirmCode();
            },

            countdown: function() {
                this.time--;
                this.countdownText = 'resemd email (' + this.time + ')';
                if (this.time == 0) {
                    this.time = 20;
                    this.countdownText = 'resend email';
                    clearInterval(this.timer);
                    this.timer = null;
                }
                console.log(this.time);
            }


        },

        computed: {
            HaveTimer: function() {
                return this.time > 0 && this.timer != null;
            }
        }

    });
</script>