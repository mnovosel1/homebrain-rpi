function check()
{
  var noError = true;

  if (!("serviceWorker" in navigator)) {
    noError = false;
    throw new Error("No Service Worker support!");
  }

  if (!("PushManager" in window)) {
    noError = false;
    throw new Error("No Push API Support!");
  }

  return noError;
};

if (check())
{
  function urlBase64ToUint8Array(base64String)
  {
    const padding = '='.repeat((4 - base64String.length % 4) % 4);
    const base64 = (base64String + padding)
      .replace(/\-/g, '+')
      .replace(/_/g, '/')
    ;
    const rawData = window.atob(base64);
    return Uint8Array.from([...rawData].map((char) => char.charCodeAt(0)));
  }

  async function subscribeUserToPush()
  {
    const registration = await navigator.serviceWorker.register('pushNotificationsServiceWorker.js');
    const subscribeOptions = {
      userVisibleOnly: true,
      applicationServerKey: urlBase64ToUint8Array("BNmz-Vsi38A9KceC-9Q9S0dTqP069I0q7JEAg3GPco6-JCIxRs3ac7YUibBtkNi0RUW9PIslpMlBddH1L2_3hqQ")
    };
    const pushSubscription = await registration.pushManager.subscribe(subscribeOptions);
    console.log('PushSubscription: ', JSON.stringify(pushSubscription));
    return pushSubscription;
  }

  subscribeUserToPush();
}
else
{
  console.log("We have Errors!");
}
