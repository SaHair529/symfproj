document.addEventListener('DOMContentLoaded', () => {
    addButtons()
    addButtonClickListeners()
})

function addButtons()
{
    let dataElems = document.querySelectorAll('pre')

    for (let i = 0; i < dataElems.length; i++) {
        dataElems[i].appendChild(renderChangeBtn())
        dataElems[i].appendChild(renderDeleteBtn())
    }
}
function renderChangeBtn()
{
    let changeBtn = document.createElement('div')
    changeBtn.textContent = 'change'
    changeBtn.classList.add('change_btn')

    return changeBtn
}
function renderDeleteBtn()
{
    let deleteBtn = document.createElement('div')
    deleteBtn.textContent = 'delete'
    deleteBtn.classList.add('delete_btn')

    return deleteBtn
}

function addButtonClickListeners()
{
    addDeleteButtonsClickListener()
    addChangeButtonsClickListener()
}
function addChangeButtonsClickListener()
{
    let changeButtons = document.querySelectorAll('.change_btn')
    for (let i = 0; i < changeButtons.length; i++) {
        addChangeBtnClickListener(changeButtons[i])
    }
}
function addDeleteButtonsClickListener()
{
    let deleteButtons = document.querySelectorAll('.delete_btn')
    for (let i = 0; i < deleteButtons.length; i++) {
        addDeleteBtnClickListener(deleteButtons[i])
    }
}

function addChangeBtnClickListener(btn)
{
    btn.addEventListener('click', () => {
        let dataEntityBlock, changeWindow, changeForm, dataEntityIdInput, dataTextArea

        dataEntityBlock = btn.parentElement.parentElement
        changeWindow = document.querySelector('.change_data_window')
        changeForm = changeWindow.querySelector('form')
        dataEntityIdInput = changeForm.querySelector('#id')
        dataTextArea = changeForm.querySelector('#data')

        dataEntityBlock.after(changeWindow)
        changeWindow.classList.add('active')
        dataEntityIdInput.value = dataEntityBlock.id
        dataTextArea.textContent = dataEntityBlock.getAttribute('data-json')
    })
}

function addDeleteBtnClickListener(btn)
{
    btn.addEventListener('click', () => {
        let dataEntityBlock = btn.parentNode.parentElement
        sendDeleteDataEntityRequest(dataEntityBlock.id)
    })
}
function sendDeleteDataEntityRequest(id)
{
    sendRequest('delete?entity_id='+id, {})
}

function sendRequest(url, data)
{
    let xhr = new XMLHttpRequest()
    xhr.open('POST', url)
    xhr.send()
    xhr.onload = () => {
        if (xhr.status === 200)
            document.location.reload()
        else alert('Ошибка при удалении элемента, обратитесь к разработчику')
    }
    // await fetch(url, {
    //     method: 'POST',
    //     body: JSON.stringify(data)
    // }).then(() => console.log('ok'))
}