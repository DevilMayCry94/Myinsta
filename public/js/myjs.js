$(document).ready(function(){
    jQuery.validator.setDefaults({
        debug: true,
        success: "valid"
    });
    $('#reg-form').validate({
        rules:{
            Name:{
                required: true,
                minlength: 1,
                maxlength: 100,
            },
            Email:{
                required: true,
                minlength: 5,
                maxlength: 45,
                email:true,
            },
            Password:{
                required: true,
                minlength: 6,
                maxlength: 16,
                regex: /^[0-9a-zA-Z\_]+$/
            },
            Confirm_password:{
                required: true,
                minlength: 6,
                maxlength: 16,
                equalTo:'#Password',
                regex: /^[0-9a-zA-Z\_]+$/
            },
        },
        messages:{
            Name:{
                required: "Введите имя пользователя",
                minlength: "Минимальная длинна - 2 символа",
                maxlength: "Максимальная длянна - 100 символов",
                characters: "Имя может содержать только буквы",
            },
            Email:{
                required: "Введите email!",
                minlength: "Email должен быть минимум 5 символов",
                maxlength: "Максимальное число символов - 45",
                email:"Введите корректный email"
            },
            Password:{
                required: "Введите пароль!",
                minlength: "Пароль должен содержать минимум 6 символов",
                maxlength: "Пароль должен содержать максимум 16 символов",
                regex: "Пароль может содержать только латинские буквы, цифры и символы нижнего подчёркивания"
            },
            Confirm_password:{
                required: "Повторите пароль",
                minlength: "Пароль должен содержать минимум 6 символов",
                maxlength: "Пароль должен содержать максимум 16 символов",
                equalTo: "Пароли не совпадают",
                regex: "Пароль может содержать только латинские буквы, цифры и символы нижнего подчёркивания"
            },
        }
    });
});