<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    {{-- jquery --}}
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>

    {{-- pusher cdn --}}
    <script src="https://js.pusher.com/7.0.3/pusher.min.js"></script>

    {{-- vue cdn --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/vue/2.6.14/vue.min.js"
        integrity="sha512-XdUZ5nrNkVySQBnnM5vzDqHai823Spoq1W3pJoQwomQja+o4Nw0Ew1ppxo5bhF2vMug6sfibhKWcNJsG8Vj9tg=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    {{-- bootstrap css cdn --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

    {{-- bootstrap js cdn --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous">
        </script>
</head>

<body>
    <div class="container" id="app">
        <h1 class="text-center mt-4">Laravel Websockets Chat Example</h1>
        <div class="card mt-4">
            <div class="card-header p-2">
                <form action="">
                    <div class="col-lg-2 col-md-3 col-sm-12 mt-2 p-2">
                        <label for="name">Name</label>
                        <input type="text" id="name" class="form-control form-control-sm" placeholder="Name"
                            v-model="name">
                    </div>
                    <div class="col-lg-1 col-md-2 col-sm-12 mt-2 p-2">
                        <button v-if="connected === false" v-on:click="connect()" type="button"
                            class="mr-2 btn btn-sm btn-primary w-100">Connect</button>
                        <button v-if="connected === true" v-on:click="disconnect()" type="button"
                            class="mr-2 btn btn-sm btn-danger w-100">Disconnect</button>
                    </div>
                </form>
                <div>
                    <p> Channels current state is: @{{ state }}</p>
                </div>
            </div>


            <div v-if="connected === true" class="card-body">

                <div class="col-12 bg-light pt-2 pb-2 mt-3">
                    <p class="p-0 m-0 ps-2 pe-2" v-for="(message, index) in incomingMessages">
                        (@{{ message.time }}) <b>@{{ message.name }}</b>
                        @{{ message.message }}
                    </p>
                </div>

                <h4 class="mt-4">Message</h4>
                <form action="">
                    <div class="row mt-2">
                        <div class="col-12 text-white" v-show="formError === true">
                            <div class="bg-danger p-2 mb-2">
                                <p class="p-0 m-0">
                                    <b>Error:</b>Invalid message
                                </p>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="form-group">
                                <textarea v-model="message" name="" id="" placeholder="Your message...."
                                    class="form-control" rows="3"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="row text-right mt-2">
                        <div class="col-lg-10">

                        </div>

                        <div class="col-lg-2">
                            <button type="button" v-on:click="sendMessage()" class="btn btn-sm btn-primary w-100">Send
                                Event</button>
                        </div>
                    </div>
                </form>
            </div>

        </div>
    </div>



    <script>
        new Vue({
            "el": "#app",
            "data": {
                connected: false,

                pusher: null,
                app: null,
                apps: {!! json_encode($apps) !!},
                logChannel: "{{ $logChannel }}",
                authEndpoint: "{{ $authEndpoint }}",
                host: "{{ $host ?? '127.0.0.1' }}",
                port: "{{ $port ?? 6001 }}",

                state: null,

                name: null,
                formError: false,
                incomingMessages: [
                ],
                message: null
            },
            mounted() {
                this.app = this.apps[0] || null
            },
            methods: {
                connect() {
                    this.pusher = new Pusher("staging", {
                        wsHost: this.host,
                        wsPort: parseInt(this.port),
                        wssPort: parseInt(this.port),
                        wsPath: this.app ? this.app.path : '',
                        disableStats: true,
                        authEndpoint: this.authEndpoint,
                        forceTLS: false,
                        auth: {
                            headers: {
                                "X_CSRF-TOKEN": "{{ csrf_token() }}",
                                "X-App-ID": this.app.id
                            }
                        },
                        enabledTransports: ['ws', "flash"]
                    });
                    this.pusher.connection.bind('state_change', (states) => {
                        this.state = states.current;
                    });
                    this.pusher.connection.bind('connected', () => {
                        this.connected = true;
                    });
                    this.pusher.connection.bind('disconneted', () => {
                        this.connected = false;
                    });
                    this.pusher.connection.bind('error', (event) => {
                        this.formError = true;

                    });

                    this.subscribeToAllChannels();
                },

                subscribeToAllChannels() {
                    [
                        "api-message"
                    ].forEach(channelName => this.subscribeToChannel(channelName));
                },
                subscribeToChannel(channelName) {
                    let inst = this;
                    this.pusher.subscribe(this.logChannel + channelName).bind("log-message", (data) => {
                        if(data.type == "api-message"){
                            console.log('data: ' , data)
                            if(data.details.includes("SendMessageEvent")){
                                let messageData = JSON.parse(data.data)
                                let utcDate = new Date(messageData.time)
                                messageData.time = utcDate.toLocaleString();
                                inst.incomingMessages.push(messageData)
                            }
                        }
                    })
                },
                disconnect() {
                    this.connected = false;
                },
                sendMessage() {
                    this.formError = true;
                    if (this.message === "" || this.message === null) {
                        this.formError = true;
                    } else {
                        $.post('/chat/send', {
                            _token: "{{ csrf_token() }}",
                            message: this.message,
                            name: this.name
                        }).fail(() => {
                            alert('Error sending message');
                        })
                    }
                }
            }
        })
    </script>
</body>

</html>