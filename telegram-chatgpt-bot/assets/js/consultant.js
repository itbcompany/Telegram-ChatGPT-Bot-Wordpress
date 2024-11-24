document.getElementById('send-button').addEventListener('click', function() {
    var message = document.getElementById('message-input').value;
    if (message) {
        var chatMessages = document.getElementById('chat-messages');
        var messageElement = document.createElement('div');
        messageElement.textContent = message;
        chatMessages.appendChild(messageElement);
        document.getElementById('message-input').value = '';

        // Отправка сообщения на сервер
        fetch('/wp-json/telegram-chatgpt-bot/send-message', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ message: message })
        }).then(response => response.json()).then(data => {
            if (data.success) {
                var responseElement = document.createElement('div');
                responseElement.textContent = data.response;
                chatMessages.appendChild(responseElement);
            }
        });
    }
});
