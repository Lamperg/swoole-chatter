const socket = new WebSocket('ws://swoole-chatter.loc/api');

socket.onopen = function() {
    console.log('Connection established');

    socket.send('Hello server!');
};

socket.onerror = ev => console.log('error happen', ev);

socket.onmessage = function(event) {
  console.log("message received:", event.data);
};
