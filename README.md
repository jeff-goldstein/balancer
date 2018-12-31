# balancer

This project was born out of a hack-a-thon to help customers through the warm-up process as they migrate to SparkPost.  I do NOT actually
inject emails in this project; I mearly guide calling processes to which ESP and/or ESP IP pool to leverage.  

At a high level, it is expected that you have an email injector that can send through many different email ESPs.  This project will keep
track of how many emails you have sent through each channel and guide you to which email channels are best to use for your next set
of emails.  (The code doesn't actually know how much you sent through each ESP pool, it only knows how many emails it has told you to send
through those channels).

For example, let's say your injector sends one email at a time for customer invoices.  Your injector will tell the 'controller' that 
you want to send 1 email with the type of invoice.  The controller will first look for any IP addresses that are being warmed up for
that type of email; and if there is any warmup quota left for the day.  If so, it will guide you to use that IP pool; if not, it will look
for pools that are already warmed up that you should use.

<continue>........
