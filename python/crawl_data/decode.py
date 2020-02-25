from js2py.pyjs import *
# setting scope
var = Scope( JS_BUILTINS )
set_global_object(var)

# Code follows:
var.registers([u'funk_it', u'dec_it'])
@Js
def PyJsHoisted_funk_it_(str_data, this, arguments, var=var):
    var = Scope({u'str_data':str_data, u'this':this, u'arguments':arguments}, var)
    var.registers([u'ac', u'tmp_arr', u'i', u'str_data', u'c3', u'c2', u'c1'])
    var.put(u'tmp_arr', Js([]))
    var.put(u'i', Js(0.0))
    var.put(u'ac', Js(0.0))
    var.put(u'c1', Js(0.0))
    var.put(u'c2', Js(0.0))
    var.put(u'c3', Js(0.0))
    var.put(u'str_data', Js(u''), u'+')
    while (var.get(u'i')<var.get(u'str_data').get(u'length')):
        var.put(u'c1', var.get(u'str_data').callprop(u'charCodeAt', var.get(u'i')))
        if (var.get(u'c1')<Js(128.0)):
            var.get(u'tmp_arr').put((var.put(u'ac',Js(var.get(u'ac').to_number())+Js(1))-Js(1)), var.get(u'String').callprop(u'fromCharCode', var.get(u'c1')))
            (var.put(u'i',Js(var.get(u'i').to_number())+Js(1))-Js(1))
        else:
            if ((var.get(u'c1')>Js(191.0)) and (var.get(u'c1')<Js(224.0))):
                var.put(u'c2', var.get(u'str_data').callprop(u'charCodeAt', (var.get(u'i')+Js(1.0))))
                var.get(u'tmp_arr').put((var.put(u'ac',Js(var.get(u'ac').to_number())+Js(1))-Js(1)), var.get(u'String').callprop(u'fromCharCode', (((var.get(u'c1')&Js(31.0))<<Js(6.0))|(var.get(u'c2')&Js(63.0)))))
                var.put(u'i', Js(2.0), u'+')
            else:
                var.put(u'c2', var.get(u'str_data').callprop(u'charCodeAt', (var.get(u'i')+Js(1.0))))
                var.put(u'c3', var.get(u'str_data').callprop(u'charCodeAt', (var.get(u'i')+Js(2.0))))
                var.get(u'tmp_arr').put((var.put(u'ac',Js(var.get(u'ac').to_number())+Js(1))-Js(1)), var.get(u'String').callprop(u'fromCharCode', ((((var.get(u'c1')&Js(15.0))<<Js(12.0))|((var.get(u'c2')&Js(63.0))<<Js(6.0)))|(var.get(u'c3')&Js(63.0)))))
                var.put(u'i', Js(3.0), u'+')
    return var.get(u'tmp_arr').callprop(u'join', Js(u''))
PyJsHoisted_funk_it_.func_name = u'funk_it'
var.put(u'funk_it', PyJsHoisted_funk_it_)
@Js
def PyJsHoisted_dec_it_(data, this, arguments, var=var):
    var = Scope({u'this':this, u'data':data, u'arguments':arguments}, var)
    var.registers([u'ac', u'tmp_arr', u'bits', u'b64', u'h2', u'data', u'h1', u'h4', u'i', u'h3', u'dec', u'o3', u'o2', u'o1'])
    var.put(u'data', var.get(u'data').callprop(u'split', Js(u'@')).callprop(u'join', Js(u'CAg')))
    var.put(u'data', var.get(u'data').callprop(u'split', Js(u'!')).callprop(u'join', Js(u'W5')))
    var.put(u'data', var.get(u'data').callprop(u'split', Js(u'*')).callprop(u'join', Js(u'CAgI')))
    var.put(u'data', var.get(u'data').callprop(u'split', Js(u'$')).callprop(u'join', Js(u'dGhl')))
    var.put(u'data', var.get(u'data').callprop(u'split', Js(u'%')).callprop(u'join', Js(u'YXN')))
    var.put(u'data', var.get(u'data').callprop(u'split', Js(u'&')).callprop(u'join', Js(u'YW')))
    var.put(u'b64', Js(u'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/='))
    var.put(u'i', Js(0.0))
    var.put(u'ac', Js(0.0))
    var.put(u'dec', Js(u''))
    var.put(u'tmp_arr', Js([]))
    if var.get(u'data').neg():
        return var.get(u'data')
    var.put(u'data', Js(u''), u'+')
    while 1:
        var.put(u'h1', var.get(u'b64').callprop(u'indexOf', var.get(u'data').callprop(u'charAt', (var.put(u'i',Js(var.get(u'i').to_number())+Js(1))-Js(1)))))
        var.put(u'h2', var.get(u'b64').callprop(u'indexOf', var.get(u'data').callprop(u'charAt', (var.put(u'i',Js(var.get(u'i').to_number())+Js(1))-Js(1)))))
        var.put(u'h3', var.get(u'b64').callprop(u'indexOf', var.get(u'data').callprop(u'charAt', (var.put(u'i',Js(var.get(u'i').to_number())+Js(1))-Js(1)))))
        var.put(u'h4', var.get(u'b64').callprop(u'indexOf', var.get(u'data').callprop(u'charAt', (var.put(u'i',Js(var.get(u'i').to_number())+Js(1))-Js(1)))))
        var.put(u'bits', ((((var.get(u'h1')<<Js(18.0))|(var.get(u'h2')<<Js(12.0)))|(var.get(u'h3')<<Js(6.0)))|var.get(u'h4')))
        var.put(u'o1', ((var.get(u'bits')>>Js(16.0))&Js(255)))
        var.put(u'o2', ((var.get(u'bits')>>Js(8.0))&Js(255)))
        var.put(u'o3', (var.get(u'bits')&Js(255)))
        if (var.get(u'h3')==Js(64.0)):
            var.get(u'tmp_arr').put((var.put(u'ac',Js(var.get(u'ac').to_number())+Js(1))-Js(1)), var.get(u'String').callprop(u'fromCharCode', var.get(u'o1')))
        else:
            if (var.get(u'h4')==Js(64.0)):
                var.get(u'tmp_arr').put((var.put(u'ac',Js(var.get(u'ac').to_number())+Js(1))-Js(1)), var.get(u'String').callprop(u'fromCharCode', var.get(u'o1'), var.get(u'o2')))
            else:
                var.get(u'tmp_arr').put((var.put(u'ac',Js(var.get(u'ac').to_number())+Js(1))-Js(1)), var.get(u'String').callprop(u'fromCharCode', var.get(u'o1'), var.get(u'o2'), var.get(u'o3')))
        if not (var.get(u'i')<var.get(u'data').get(u'length')):
            break
    var.put(u'dec', var.get(u'tmp_arr').callprop(u'join', Js(u'')))
    var.put(u'dec', var.get(u"this").callprop(u'funk_it', var.get(u'dec')))
    return var.get(u'dec')
PyJsHoisted_dec_it_.func_name = u'dec_it'
var.put(u'dec_it', PyJsHoisted_dec_it_)

def decode(str_data):
    return str(var.get(u'dec_it')(Js(u'' + str_data)))

def decode_html(data):
    
    current_string = ''
    end_index = 0

    while 1:
        start_index = data.find("dec_it('", end_index)

        if start_index == -1:
            break

        end_index = data.find("')", start_index)
            
        temp_string = data[start_index+8:end_index]

        if len(current_string) < len(temp_string):
            current_string = temp_string

    return decode(current_string)