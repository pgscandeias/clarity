# Data model


## User

+ created
+ name
+ email
+ tokens.login
+ tokens.auth
+ accounts[]
    + _id
    + name
    + slug
    + roles[]


## Account

+ created
+ name
+ slug
+ users[]
    + _id
    + name
    + email
    + roles[]
+ chats[] #Ref: Chat


## Chat

+ title
+ tags[]
+ created
+ updated
+ messages[]
    + created
    + text

