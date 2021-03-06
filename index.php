<html>
<head>
    <title>Commando</title>
    <link href="https://fonts.googleapis.com/css?family=PT+Mono" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/vue/dist/vue.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/vue-resource@1.5.1"></script>
    <style>
        * {
            box-sizing: border-box;
        }
        .before {max-width: 16.66%;}
        .content {width: 75%;}
        .load {
            width: 8.33%;
            float: right;
            padding: 15px;
        }

        .before, .content {
            float: left;
            padding: 15px;
        }

        body {
            font-family: 'PT Mono', monospace;
            color: #bb51cc;
            background-color: black;
        }
        input {
            font-family: 'PT Mono', monospace;
            color: #bb51cc;
            background-color: black;
            border: none;
            width: 90%;
            height: 20px;
            outline-style:none;
            box-shadow:none;
            border-color:transparent;
        }
        .prompt {
            display: -ms-flexbox;
            display: -webkit-flex;
            display: flex;

            -ms-flex-align: center;
            -webkit-align-items: center;
            -webkit-box-align: center;

            align-items: center;
            height: 30px;
        }
        .path {
            display: inline-flex;
            max-width: 80%;
            overflow: hidden;
            text-overflow: ellipsis;
        }
    </style>
</head>
<body>
<div id="commando">
    <pre>{{output}}</pre>
    <div class="prompt">
        <span class="before">[<span class="path">{{cmdPath}}</span>] $</span>
        <input class="content" v-model="cmd" ref="cmd" id="cmd" @keyup.enter='send' @keyup.up='lastCmd' @keyup.ctrl.shift.75='clearCmdAfter' @keyup.ctrl.shift.107='clearCmdAfter'/>
        <span v-if="loading" class="load"><img src="loading.svg"/></span>
    </div>
</div>
<script>

    function scroll() {
        setTimeout(function() {
            document.body.scrollTop = document.body.scrollHeight;
        }, 50);
    }

    var robot = "   ,--.\n" +
        "  |__**|\n" +
        "  |//  |\n" +
        "  /o|__|  [Commando Web Terminal]\n\n" +
        "type help or ? for a list of commands and shortcuts\n";

    var vue = new Vue({
        el: '#commando',
        data: {
            cmd: '',
            output: robot,
            loading: false,
            cmdPath: 'loading path...',
            history: [],
            currentHistory: 0
        },
        methods: {
            send: function() {
                this.currentHistory = this.history.length;
                switch(this.cmd) {
                    case 'clear':
                        this.clear();
                        break;
                    case 'help':
                    case '?':
                        this.help();
                        break;
                    case 'history':
                        this.showHistory();
                        break;
                    case 'history -c':
                        this.clearHistory();
                        break;
                    default:
                        this.loading = true;
                        this.$http.post('exec.php', {command:this.cmd}).then(function (response) {
                            this.cmdPath = response.body.path;
                            if (this.cmd != '') {
                                this.output += "\n";
                                this.saveCommand();
                            }
                            $this = this;
                            if (response.body.output) {
                                response.body.output.forEach(function(data){
                                    $this.output += data + "\n";
                                });
                            }
                            this.clearCmd();
        
                            this.loading = false;
                            document.getElementById('cmd').focus();
                            scroll();
                        });
                }
            },
            init: function() {
                document.getElementById('cmd').focus();
            },
            saveCommand() {
                this.output += "\n[" + this.cmdPath + "] $ " + this.cmd + "\n";
                this.history.push(this.cmd);
            },
            clear: function() {
                this.saveCommand();
                this.output = '';
                this.clearCmd();
            },
            clearCmd: function() {
                this.cmd = '';
            },
            clearCmdAfter: function() {
                this.cmd = this.cmd.substring(0, this.$refs.cmd.selectionStart);
            },
            help: function() {
                this.saveCommand();
                var help = "> Commands\n" + 
                    "help or ?: Show this tool\n" + 
                    "clear: Clear the terminal\n" +
                    "history: Show the commands history\n" +
                    "history -c: Clear the commands history\n" + 
                    "> shortcuts\n" + 
                    "up key: Show last commands\n" + 
                    "control + shift + k: Clear after position";
                this.output += help;
                this.clearCmd();
            },
            showHistory() {
                this.saveCommand();
                $this = this;
                this.history.forEach(function(item, index){
                    $this.output += ((index > 0) ? "\n" : "") + item;
                });
                this.clearCmd();
            },
            clearHistory: function() {
                this.history = [];
                this.clearCmd();
            },
            lastCmd: function() {
                if (this.currentHistory > 0) {
                    this.currentHistory--;
                }
                this.cmd = this.history[this.currentHistory];
            }
        },
        mounted() {
            this.init();
            this.send();
        }
    });
</script>
</body>
</html>
