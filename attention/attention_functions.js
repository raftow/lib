function atalert(tit, body)
{
        new Attention.Alert({
                title: tit,
                content: body,
                afterClose: () => {                                        
        }
        });
}

