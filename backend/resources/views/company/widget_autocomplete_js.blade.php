(function() {
    window.NamerhaAddressWidget = function(options) {
        var opts = Object.assign({
            apiKey: '',
            container: 'namerha-address-widget',
            placeholder: 'ابحث عن عنوان أو منطقة...',
            language: 'ar'
        }, options);

        var container = document.getElementById(opts.container);
        if (!container) return;

        var dir = opts.language === 'ar' ? 'rtl' : 'ltr';
        var apiBase = '{{ $apiBase }}';

        container.innerHTML = `
            <style>
                /* Microsoft Fluent UI Inspired Styles for Widget */
                .namerha-widget { 
                    font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, Roboto, sans-serif; 
                    direction: ${dir}; 
                    position: relative;
                }
                .namerha-widget input {
                    width: 100%; 
                    padding: 8px 12px; 
                    border: 1px solid #8a8886; 
                    border-bottom: 2px solid #8a8886;
                    border-radius: 2px;
                    font-family: inherit; 
                    font-size: 14px; 
                    color: #323130;
                    box-sizing: border-box; 
                    outline: none;
                    transition: border-color 0.1s;
                    background: #ffffff;
                }
                .namerha-widget input:focus { 
                    border-color: #0078d4; 
                    border-bottom-color: #0078d4;
                }
                .namerha-widget input::placeholder {
                    color: #605e5c;
                }
                .namerha-results {
                    list-style: none; 
                    margin: 4px 0 0; 
                    padding: 0; 
                    border: 1px solid #edebe9;
                    border-radius: 2px; 
                    max-height: 250px; 
                    overflow-y: auto; 
                    display: none; 
                    background: #ffffff;
                    position: absolute;
                    width: 100%;
                    z-index: 9999;
                    box-shadow: 0 3.2px 7.2px 0 rgba(0,0,0,0.132), 0 0.6px 1.8px 0 rgba(0,0,0,0.108);
                }
                .namerha-results li {
                    padding: 8px 12px; 
                    cursor: pointer; 
                    font-size: 14px; 
                    color: #323130;
                    border-bottom: 1px solid #f3f2f1;
                    transition: background 0.1s;
                }
                .namerha-results li:last-child {
                    border-bottom: none;
                }
                .namerha-results li:hover { 
                    background: #f3f2f1; 
                }
                .namerha-result-detail { 
                    font-size: 12px; 
                    color: #605e5c; 
                    margin-top: 4px; 
                }
                .namerha-selected-box {
                    margin-top: 12px; 
                    padding: 10px 12px; 
                    background: #f3f2f1; 
                    border-left: 3px solid #0078d4; 
                    border-radius: 2px; 
                    color: #323130; 
                    font-size: 13px;
                }
            </style>
            <div class="namerha-widget">
                <input type="text" id="namerha-search-input" placeholder="${opts.placeholder}" autocomplete="off">
                <ul class="namerha-results" id="namerha-results-list"></ul>
                <div id="namerha-selected" style="display: none;"></div>
            </div>
        `;

        var input = document.getElementById('namerha-search-input');
        var resultsList = document.getElementById('namerha-results-list');
        var selectedDiv = document.getElementById('namerha-selected');
        var debounceTimer;

        input.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            var query = this.value.trim();
            if (query.length < 2) {
                resultsList.style.display = 'none';
                return;
            }
            debounceTimer = setTimeout(function() {
                fetch(apiBase + '/api/v1/b2b/addresses/search?query=' + encodeURIComponent(query), {
                    headers: { 'X-API-Key': opts.apiKey }
                })
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    var results = data.data || [];
                    resultsList.innerHTML = '';
                    if (results.length === 0) {
                        resultsList.style.display = 'none';
                        return;
                    }
                    results.forEach(function(item) {
                        var li = document.createElement('li');
                        
                        var mainText = document.createElement('div');
                        mainText.style.fontWeight = '600';
                        mainText.textContent = item.address_str || item.neighborhood || 'عنوان غير معروف';
                        li.appendChild(mainText);

                        var detail = document.createElement('div');
                        detail.className = 'namerha-result-detail';
                        detail.textContent = (item.neighborhood ? 'الحي: ' + item.neighborhood : '') + (item.s2_token ? ' | الرمز المرجعي: ' + item.s2_token : '');
                        li.appendChild(detail);

                        li.addEventListener('click', function() {
                            selectedDiv.style.display = 'block';
                            selectedDiv.className = 'namerha-selected-box';
                            selectedDiv.innerHTML = '<strong style="color:#0078d4;">الموقع المحدد:</strong><br>' + (item.address_str || item.neighborhood);
                            
                            resultsList.style.display = 'none';
                            input.value = item.address_str || item.neighborhood || query;

                            if (opts.onSelect) opts.onSelect(item);
                        });
                        resultsList.appendChild(li);
                    });
                    resultsList.style.display = 'block';
                })
                .catch(function(e) { console.error('Namerha Widget Error:', e); });
            }, 300);
        });

        document.addEventListener('click', function(e) {
            if (!container.contains(e.target)) {
                resultsList.style.display = 'none';
            }
        });
    };
})();
