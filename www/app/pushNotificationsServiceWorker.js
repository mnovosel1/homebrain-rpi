self.addEventListener("push", function(event) {
  if (event.data) {
      console.log("Push event!! ", event.data.text());

      const notificationTitle = 'HomeBrain';
      const notificationOptions = {
        body: event.data.text(),
        icon: 'images/homebrain.png',
        badge:  'images/homebrain_96x96.png',
        sound: 'sounds/r2d2_a.wav'
      };
  
    self.registration.showNotification(notificationTitle, notificationOptions);
  }
  else
  {
    console.log("Push event but no data");
  }
});
