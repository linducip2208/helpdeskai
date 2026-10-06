/* HelpDesk AI embeddable chat widget (no dependencies, no CDN).
 * Usage: <script src="https://YOUR-HELPDESK/widget.js" data-endpoint="https://YOUR-HELPDESK/widget/tickets" data-title="Need help?" data-color="#066fd1" async></script>
 */
(function () {
    var tag = document.currentScript || (function () { var s = document.getElementsByTagName('script'); return s[s.length - 1]; })();
    var endpoint = tag.getAttribute('data-endpoint') || '/widget/tickets';
    var title = tag.getAttribute('data-title') || 'Need help?';
    var color = tag.getAttribute('data-color') || '#066fd1';

    var css = '#hdk-widget-btn{position:fixed;right:20px;bottom:20px;z-index:99998;width:56px;height:56px;border-radius:50%;border:0;cursor:pointer;color:#fff;font-size:24px;box-shadow:0 4px 14px rgba(0,0,0,.25);background:' + color + ';}'
        + '#hdk-widget-panel{position:fixed;right:20px;bottom:88px;z-index:99998;width:320px;max-width:calc(100vw - 40px);background:#fff;border-radius:12px;box-shadow:0 8px 30px rgba(0,0,0,.25);overflow:hidden;font-family:system-ui,-apple-system,"Segoe UI",Roboto,sans-serif;display:none;}'
        + '#hdk-widget-head{padding:12px 16px;color:#fff;font-weight:600;background:' + color + ';}'
        + '#hdk-widget-body{padding:16px;display:flex;flex-direction:column;gap:10px;}'
        + '#hdk-widget-body input,#hdk-widget-body textarea{width:100%;box-sizing:border-box;padding:8px 10px;border:1px solid #d7dde3;border-radius:8px;font-size:14px;}'
        + '#hdk-widget-send{padding:10px;border:0;border-radius:8px;color:#fff;font-weight:600;cursor:pointer;background:' + color + ';}'
        + '#hdk-widget-msg{font-size:13px;color:#2fb344;min-height:18px;}'
        + '#hdk-widget-hp{position:absolute;left:-9999px;}';

    var style = document.createElement('style');
    style.textContent = css;
    document.head.appendChild(style);

    var btn = document.createElement('button');
    btn.id = 'hdk-widget-btn';
    btn.type = 'button';
    btn.setAttribute('aria-label', 'Open support chat');
    btn.textContent = '?';
    document.body.appendChild(btn);

    var panel = document.createElement('div');
    panel.id = 'hdk-widget-panel';
    panel.innerHTML = '<div id="hdk-widget-head"></div>'
        + '<div id="hdk-widget-body">'
        + '<input id="hdk-name" maxlength="120" placeholder="Your name" autocomplete="name">'
        + '<input id="hdk-email" type="email" maxlength="255" placeholder="Email" autocomplete="email">'
        + '<input id="hdk-subject" maxlength="255" placeholder="Subject">'
        + '<textarea id="hdk-message" rows="4" maxlength="5000" placeholder="How can we help?"></textarea>'
        + '<input id="hdk-website" type="text" tabindex="-1" autocomplete="off">'
        + '<div id="hdk-widget-msg" role="status"></div>'
        + '<button id="hdk-widget-send" type="button">Send message</button>'
        + '</div>';
    panel.querySelector('#hdk-widget-head').textContent = title;
    panel.querySelector('#hdk-website').id = 'hdk-widget-hp';
    document.body.appendChild(panel);

    btn.addEventListener('click', function () {
        panel.style.display = panel.style.display === 'block' ? 'none' : 'block';
    });

    panel.querySelector('#hdk-widget-send').addEventListener('click', function () {
        var msg = panel.querySelector('#hdk-widget-msg');
        var payload = {
            name: panel.querySelector('#hdk-name').value.trim(),
            email: panel.querySelector('#hdk-email').value.trim(),
            subject: panel.querySelector('#hdk-subject').value.trim(),
            message: panel.querySelector('#hdk-message').value.trim(),
            website: panel.querySelector('#hdk-widget-hp').value
        };
        msg.style.color = '#2fb344';
        if (!payload.name || !payload.email || !payload.subject || !payload.message) {
            msg.style.color = '#d63939';
            msg.textContent = 'Please fill in all fields.';
            return;
        }
        msg.textContent = 'Sending…';
        fetch(endpoint, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify(payload)
        }).then(function (res) {
            if (!res.ok) throw new Error('HTTP ' + res.status);
            return res.json();
        }).then(function (json) {
            msg.textContent = (json && json.message) || 'Message received.';
            panel.querySelector('#hdk-message').value = '';
            panel.querySelector('#hdk-subject').value = '';
        }).catch(function () {
            msg.style.color = '#d63939';
            msg.textContent = 'Could not send. Please try again later.';
        });
    });
})();
