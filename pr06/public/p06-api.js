let token = '';
const $ = id => document.getElementById(id);
let lastItem = null;
function say(text) { $('feedback').textContent = text; }
async function send(method, path, body) {
    const headers = {};
    if (token) headers.Authorization = 'Bearer ' + token;
    if (body !== undefined) headers['Content-Type'] = 'application/json';
    // Пароли и полный токен не сохраняются в истории на странице.
    const shown = path === '/api/login' ? {...body,password:'[скрыт]'} : body;
    $('request').textContent = method+' '+path+'\n'+(token ? 'Authorization: Bearer [скрыт]\n' : '')+(shown === undefined ? '' : JSON.stringify(shown,null,2));
    try {
        const response = await fetch(path, {method,headers,credentials:'omit',body:body === undefined ? undefined : JSON.stringify(body)});
        const text = await response.text();
        let data = null;
        if(text) { try { data = JSON.parse(text); } catch { data = {title:'Ответ не является JSON',body:text}; } }
        const shownData = data && data.access_token ? {...data,access_token:'[получен; хранится только в памяти]'} : data;
        $('response').textContent = 'HTTP '+response.status+'\nContent-Type: '+(response.headers.get('Content-Type')||'')+'\nX-Request-Id: '+(response.headers.get('X-Request-Id')||'')+'\n'+(shownData ? JSON.stringify(shownData,null,2) : '(тело отсутствует)');
        document.querySelectorAll('[data-error]').forEach(el=>el.textContent='');
        if(data?.errors) for(const [field,message] of Object.entries(data.errors)) { const el=document.querySelector('[data-error="'+field+'"]'); if(el) el.textContent=message; }
        say(response.ok ? 'Операция выполнена: HTTP '+response.status : (data?.title||'Ошибка запроса')+' (HTTP '+response.status+')');
        if(data?.items) cards(data.items);
        if(data?.item) {lastItem=data.item; cards([lastItem]);}
        return {response,data};
    } catch { say('Не удалось отправить запрос. Проверьте Apache и адрес страницы.'); return {}; }
}
function cards(items) {
    $('resources').replaceChildren();
    if(!items.length) {const el=document.createElement('p');el.textContent='Записи не найдены';$('resources').append(el);}
    for(const item of items) {
        const card=document.createElement('article');card.className='api-resource';
        const title=document.createElement('h3');title.textContent=item.title;
        const values=document.createElement('p');values.textContent='ID '+item.id+' · '+item.category+' · количество '+item.quantity+' · цена '+item.priceMinor+' · владелец '+(item.ownerId??'не указан');
        const button=document.createElement('button');button.type='button';button.textContent='Выбрать запись';button.addEventListener('click',()=>{lastItem=item;for(const field of ['id','title','category','quantity','priceMinor'])$(field).value=item[field];say('Выбрана запись '+item.id);});
        card.append(title,values,button);$('resources').append(card);
    }
}
$('login').addEventListener('submit',async event=>{event.preventDefault();const password=$('password').value;const result=await send('POST','/api/login',{email:$('email').value,password});$('password').value='';if(result.response?.ok){token=result.data.access_token;$('identity').textContent='JWT получен для '+result.data.user.name+'; роль при входе: '+result.data.user.role+'; срок 15 минут';}});
$('clear').addEventListener('click',()=>{token='';$('identity').textContent='Гость: JWT отсутствует';say('Токен удалён из памяти страницы. Выданный JWT сохраняет срок действия.');});
$('search').addEventListener('submit',event=>{event.preventDefault();send('GET','/api/items?q='+encodeURIComponent($('query').value));});
$('read').addEventListener('click',()=>send('GET','/api/items/'+$('id').value));
$('item').addEventListener('submit',event=>event.preventDefault());
function payload(){return {title:$('title').value,category:$('category').value,quantity:$('quantity').valueAsNumber,priceMinor:$('priceMinor').valueAsNumber};}
$('create').addEventListener('click',()=>send('POST','/api/items',payload()));
$('update').addEventListener('click',()=>send('PUT','/api/items/'+$('id').value,payload()));
$('delete').addEventListener('click',async()=>{if(!confirm('Удалить запись '+$('id').value+'?'))return;const r=await send('DELETE','/api/items/'+$('id').value);if(r.response?.ok)$('resources').replaceChildren();});
send('GET','/api/items');
