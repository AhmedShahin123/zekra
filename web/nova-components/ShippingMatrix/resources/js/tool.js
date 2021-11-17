Nova.booting((Vue, router, store) => {
  router.addRoutes([
    {
      name: 'shipping',
      path: '/shipping',
      component: require('./components/Tool'),
    },
  ])
})
