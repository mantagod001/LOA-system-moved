(function () {
    var dataNode = document.getElementById('faq-chat-data');
    var panel = document.getElementById('faq-chat');
    var toggle = document.getElementById('faq-chat-toggle');
    var log = document.getElementById('faq-chat-log');
    var list = document.getElementById('faq-chat-list');
    var form = document.getElementById('faq-chat-form');
    var input = document.getElementById('faq-chat-input');

    if (!dataNode || !panel || !toggle || !log || !list || !form || !input) {
        return;
    }

    var entries = JSON.parse(dataNode.textContent || '[]');

    function escapeText(value) {
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');
    }

    function addMessage(text, fromUser) {
        var item = document.createElement('p');
        item.className = fromUser
            ? 'ml-8 rounded-xl bg-maroon-800 px-3 py-2 text-cream-50'
            : 'mr-6 rounded-xl bg-cream-100 px-3 py-2 leading-relaxed text-maroon-900';
        item.innerHTML = escapeText(text);
        log.appendChild(item);
        log.scrollTop = log.scrollHeight;
    }

    function reply(entry) {
        addMessage(entry.question, true);
        addMessage(entry.answer, false);
    }

    function match(text) {
        var query = text.toLowerCase();
        var best = null;
        var bestScore = 0;

        entries.forEach(function (entry) {
            var score = 0;
            entry.keywords.forEach(function (keyword) {
                var pattern = new RegExp('(^|[^a-z0-9])' + keyword.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + '([^a-z0-9]|$)');
                if (pattern.test(query)) {
                    score += keyword.length;
                }
            });
            if (score > bestScore) {
                best = entry;
                bestScore = score;
            }
        });

        return bestScore > 0 ? best : null;
    }

    var groups = [];
    entries.forEach(function (entry, index) {
        var group = null;
        groups.forEach(function (item) {
            if (item.name === entry.group) {
                group = item;
            }
        });
        if (!group) {
            group = { name: entry.group || 'Questions', options: [] };
            groups.push(group);
        }
        group.options.push({ index: index, question: entry.question });
    });

    groups.forEach(function (group) {
        var section = document.createElement('optgroup');
        section.label = group.name;
        group.options.forEach(function (option) {
            var item = document.createElement('option');
            item.value = String(option.index);
            item.textContent = option.question;
            section.appendChild(item);
        });
        list.appendChild(section);
    });

    list.addEventListener('change', function () {
        if (list.value === '') {
            return;
        }
        reply(entries[Number(list.value)]);
        list.value = '';
    });

    addMessage('Choose a question from the list, or type your own.', false);

    toggle.addEventListener('click', function () {
        var open = panel.classList.toggle('hidden') === false;
        toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        toggle.textContent = open ? 'Close help' : 'Ask about LOA';
        if (open) {
            input.focus();
        }
    });

    form.addEventListener('submit', function (event) {
        event.preventDefault();
        var text = input.value.trim();
        if (!text) {
            return;
        }
        input.value = '';
        var entry = match(text);
        if (!entry) {
            addMessage(text, true);
            addMessage('Choose a question from the list. You can ask how to check status, what to attach, what happens if it is not approved, or how to resubmit.', false);
            return;
        }
        addMessage(text, true);
        addMessage(entry.answer, false);
    });
})();
