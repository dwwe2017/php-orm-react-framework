let deleteButton = $(".toolbar:first").find(".dropdown-menu:first").find("i.icon-trash:first").parent("a");
let checkboxValues = $(".table:first").find("tbody:first").find("input[type=checkbox]");
let formData = new FormData();

$.each(checkboxValues, function (key, val) {
    formData.append($(val).attr('name'), this.is(':checked'))
});

deleteButton.click(function (event) {
    formData.submit();
});