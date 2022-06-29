/* eslint-disable */
(function () {
  tinymce.create('tinymce.plugins.ameliaBookingPlugin', {

    init: function (editor) {
      if (!('wpAmeliaLabels' in window)) {
        return
      }

      let win = null

      let entities = null
      let categories = null
      let services = null
      let employees = null
      let locations = null
      let servicesList = null
      let packages = null
      let events = null
      let tags = null
      let catalogView = null

      let setAndOpenEditor = function (view) {
        editor.windowManager.close()

        let viewBody = [{
          type: 'listbox',
          name: 'am_view_type',
          label: wpAmeliaLabels.select_view,
          values: [
            {value: 'stepbooking', text: 'Step Booking (Beta)', classes: 'am-step-booking-shortcode'},
            {value: 'booking', text: 'Booking'},
            {value: 'search', text: 'Search'},
            {value: 'catalog', text: 'Catalog'},
            {value: 'events', text: 'Events'},
            {value: 'customer_panel', text: 'Customer Panel'},
            {value: 'employee_panel', text: 'Employee Panel'},
          ],
          value: view,
          onSelect: function () {
            setAndOpenEditor(this.value())
          }
        },
        ]

        let filterItems = null

        // set view
        switch (view) {
          case ('booking'):
          case ('stepbooking'):

            // Filter
            filterItems = [
              {
                type: 'listbox',
                name: 'am_booking_category',
                label: wpAmeliaLabels.select_category,
                classes: 'am-booking-categories',
                values: [{
                  value: 0,
                  text: wpAmeliaLabels.show_all_categories
                }].concat(categories),
              },
              {
                type: 'listbox',
                name: 'am_booking_service',
                label: wpAmeliaLabels.select_service,
                classes: 'am-booking-services',
                values: [{
                  value: 0,
                  text: wpAmeliaLabels.show_all_services
                }].concat(services),
              },
              {
                type: 'listbox',
                name: 'am_booking_employee',
                label: wpAmeliaLabels.select_employee,
                classes: 'am-booking-employees',
                values: [{
                  value: 0,
                  text: wpAmeliaLabels.show_all_employees
                }].concat(employees),
              },
            ]

            if (locations.length) {
              filterItems.push({
                type: 'listbox',
                name: 'am_booking_location',
                label: wpAmeliaLabels.select_location,
                classes: 'am-booking-locations',
                values: [{
                  value: 0,
                  text: wpAmeliaLabels.show_all_locations
                }].concat(locations),
              })
            }

            if (packages.length) {
              viewBody.push({
                type: 'listbox',
                name: 'am_show',
                values: [
                  {value: '', text: wpAmeliaLabels.show_all},
                  {value: 'services', text: wpAmeliaLabels.services},
                  {value: 'packages', text: wpAmeliaLabels.packages}
                ],
                classes: 'am-show',
              })
            }

            viewBody.push({
              type: 'checkbox',
              name: 'am_booking_filter',
              label: wpAmeliaLabels.filter,
              classes: 'am-booking-filter',
              onChange: function () {
                let filterForm = win.find('#am_booking_panel')
                filterForm.visible(!filterForm.visible())
              }
            })

            viewBody.push({
              type: 'form',
              name: 'am_booking_panel',
              classes: 'am-booking-panel',
              items: filterItems,
              visible: false,
            })

            break
          case ('search'):
            // Filter
            viewBody.push({
              type: 'checkbox',
              name: 'am_search_date',
              label: wpAmeliaLabels.search_date,
              classes: 'am-search-date',
            })

            if (packages.length) {
              viewBody.push({
                type: 'listbox',
                name: 'am_show',
                values: [
                  {value: '', text: wpAmeliaLabels.show_all},
                  {value: 'services', text: wpAmeliaLabels.services},
                  {value: 'packages', text: wpAmeliaLabels.packages}
                ],
                classes: 'am-show',
              })
            }

            break
          case ('catalog'):
            var catalogOptions = [
              {value: 'catalog', text: wpAmeliaLabels.show_catalog},
              {value: 'category', text: wpAmeliaLabels.show_category},
              {value: 'service', text: wpAmeliaLabels.show_service}
            ]

            if (packages.length) {
              catalogOptions.push({value: 'package', text: wpAmeliaLabels.show_package})
            }

            viewBody.push({
              type: 'listbox',
              name: 'am_catalog_view_type',
              label: wpAmeliaLabels.select_catalog_view,
              values: catalogOptions,
              classes: 'am-catalog-view-type',
              onSelect: function () {
                catalogView = this.value()

                let categoryElement = win.find('#am_category')
                let serviceElement = win.find('#am_service')
                let packageElement = win.find('#am_package')
                let showElement = win.find('#am_show')

                if (catalogView === 'category') {
                  categoryElement.visible(true)
                  serviceElement.visible(false)
                  packageElement.visible(false)
                  showElement.visible(true)
                } else if (catalogView === 'service') {
                  categoryElement.visible(false)
                  serviceElement.visible(true)
                  packageElement.visible(false)
                  showElement.visible(false)
                } else if (catalogView === 'package') {
                  categoryElement.visible(false)
                  serviceElement.visible(false)
                  packageElement.visible(true)
                  showElement.visible(false)
                } else {
                  categoryElement.visible(false)
                  serviceElement.visible(false)
                  packageElement.visible(false)
                  showElement.visible(true)
                }
              },
            })

            // Category
            viewBody.push({
                type: 'listbox',
                name: 'am_category',
                values: categories,
                classes: 'am-categories',
              }
            )

            // Service
            viewBody.push({
              type: 'listbox',
              name: 'am_service',
              values: services,
              classes: 'am-services',
            })

            if (packages.length) {
              // Package
              viewBody.push({
                type: 'listbox',
                name: 'am_package',
                values: packages,
                classes: 'am-packages',
              })

              // Service
              viewBody.push({
                type: 'listbox',
                name: 'am_show',
                values: [
                  {value: '', text: wpAmeliaLabels.show_all},
                  {value: 'services', text: wpAmeliaLabels.services},
                  {value: 'packages', text: wpAmeliaLabels.packages}
                ],
                classes: 'am-show',
              })
            }

            // Filter
            filterItems = [
              {
                type: 'listbox',
                name: 'am_booking_employee',
                label: wpAmeliaLabels.select_employee,
                classes: 'am-booking-employees',
                values: [{
                  value: 0,
                  text: wpAmeliaLabels.show_all_employees
                }].concat(employees),
              },
            ]

            if (locations.length) {
              filterItems.push({
                type: 'listbox',
                name: 'am_booking_location',
                label: wpAmeliaLabels.select_location,
                classes: 'am-booking-locations',
                values: [{
                  value: 0,
                  text: wpAmeliaLabels.show_all_locations
                }].concat(locations),
              })
            }

            viewBody.push({
              type: 'checkbox',
              name: 'am_booking_filter',
              label: wpAmeliaLabels.filter,
              classes: 'am-booking-filter',
              style: '',
              onChange: function () {
                let filterForm = win.find('#am_booking_panel')
                filterForm.visible(!filterForm.visible())
              }
            })

            viewBody.push({
              type: 'form',
              name: 'am_booking_panel',
              classes: 'am-booking-panel',
              items: filterItems,
              visible: false,
            })

            break

          case ('events'):
            // Filter
            filterItems = [
              {
                type: 'listbox',
                name: 'am_booking_event',
                label: wpAmeliaLabels.select_event,
                classes: 'am-booking-events',
                values: [{
                  value: 0,
                  text: wpAmeliaLabels.show_all_events
                }].concat(events),
              },
              {
                type: 'checkbox',
                name: 'am_booking_event_recurring',
                label: wpAmeliaLabels.recurring_event,
                classes: 'am-recurring-event',
              },
              {
                type: 'listbox',
                name: 'am_booking_tag',
                label: wpAmeliaLabels.select_tag,
                classes: 'am-booking-tags',
                values: [{
                  value: 0,
                  text: wpAmeliaLabels.show_all_tags
                }].concat(tags),
              }
            ]

            viewBody.push({
              type: 'checkbox',
              name: 'am_booking_filter',
              label: wpAmeliaLabels.filter,
              classes: 'am-booking-filter',
              onChange: function () {
                let filterForm = win.find('#am_booking_panel')
                filterForm.visible(!filterForm.visible())
              }
            })

            viewBody.push({
              type: 'form',
              name: 'am_booking_panel',
              classes: 'am-booking-panel',
              items: filterItems,
              visible: false,
            })

            viewBody.push({
              type: 'listbox',
              name: 'am_booking_event_view_type',
              label: wpAmeliaLabels.show_event_view_type,
              values: [
                {value: 'list', text: wpAmeliaLabels.show_event_view_list},
                {value: 'calendar', text: wpAmeliaLabels.show_event_view_calendar}
              ],
              classes: 'am-booking-events-view',
            })

            break

          case ('customer_panel'):
          case ('employee_panel'):

            viewBody.push({
              type: 'checkbox',
              name: 'am_cabinet_appointments',
              label: wpAmeliaLabels.appointments,
              classes: 'am_cabinet_appointments',
            })

            viewBody.push({
              type: 'checkbox',
              name: 'am_cabinet_events',
              label: wpAmeliaLabels.events,
              classes: 'am_cabinet_events',
            })

            if (view === 'employee_panel') {
              viewBody.push({
                type: 'checkbox',
                name: 'am_cabinet_profile',
                label: wpAmeliaLabels.profile,
                classes: 'am_cabinet_profile',
              })
            }

            break
        }

        // Selector
        viewBody.push({
            type: 'textbox',
            label: wpAmeliaLabels.manually_loading,
            name: 'am_trigger',
            classes: 'am-text',
            tooltip: wpAmeliaLabels.manually_loading_description
          }
        )

        // open editor
        win = editor.windowManager.open({
          title: 'Amelia Booking',
          width: 500,
          height: 350,
          body: viewBody,
          onSubmit: function (e) {
            let shortCodeString = ''

            switch (view) {
              case ('booking'):
              case ('stepbooking'):
                if (e.data.am_booking_service) {
                  shortCodeString += ' service=' + e.data.am_booking_service
                } else if (e.data.am_booking_category) {
                  shortCodeString += ' category=' + e.data.am_booking_category
                } else if (e.data.am_booking_package) {
                  shortCodeString += ' package=' + e.data.am_booking_package
                }

                if (e.data.am_booking_employee) {
                  shortCodeString += ' employee=' + e.data.am_booking_employee
                }

                if (e.data.am_booking_location) {
                  shortCodeString += ' location=' + e.data.am_booking_location
                }

                if (e.data.am_show) {
                  shortCodeString += ' show=' + e.data.am_show
                }

                if (e.data.am_trigger) {
                  shortCodeString += ' trigger=' + e.data.am_trigger
                }

                editor.insertContent((view === 'stepbooking' ? '[ameliastepbooking' : '[ameliabooking') + shortCodeString + ']')

                break

              case ('search'):
                if (e.data.am_search_date) {
                  shortCodeString += ' today=1'
                }

                if (e.data.am_trigger) {
                  shortCodeString += ' trigger=' + e.data.am_trigger
                }

                if (e.data.am_show) {
                  shortCodeString += ' show=' + e.data.am_show
                }

                editor.insertContent('[ameliasearch' + shortCodeString + ']')

                break

              case ('catalog'):
                if (e.data.am_booking_employee) {
                  shortCodeString += ' employee=' + e.data.am_booking_employee
                }

                if (e.data.am_booking_location) {
                  shortCodeString += ' location=' + e.data.am_booking_location
                }

                if (e.data.am_show && catalogView !== 'service' && catalogView !== 'package') {
                  shortCodeString += ' show=' + e.data.am_show
                }

                if (e.data.am_trigger) {
                  shortCodeString += ' trigger=' + e.data.am_trigger
                }

                if (catalogView === 'category') {
                  editor.insertContent('[ameliacatalog category=' + e.data.am_category + shortCodeString + ']')
                } else if (catalogView === 'service') {
                  editor.insertContent('[ameliacatalog service=' + e.data.am_service + shortCodeString + ']')
                } else if (catalogView === 'package') {
                  editor.insertContent('[ameliacatalog package=' + e.data.am_package + shortCodeString + ']')
                } else {
                  editor.insertContent('[ameliacatalog' + shortCodeString + ']')
                }

                break

              case ('events'):
                if (e.data.am_booking_event) {
                  shortCodeString += ' event=' + e.data.am_booking_event

                  if (e.data.am_booking_event_recurring) {
                    shortCodeString += ' recurring=1'
                  }
                }

                if (e.data.am_booking_event_view_type) {
                  shortCodeString += ' type=' + "'" + e.data.am_booking_event_view_type + "'"
                }

                if (e.data.am_booking_tag) {
                  shortCodeString += ' tag=' + "'" + e.data.am_booking_tag + "'"
                }

                if (e.data.am_trigger) {
                  shortCodeString += ' trigger=' + e.data.am_trigger
                }

                editor.insertContent('[ameliaevents' + shortCodeString + ']')

                break

              case ('customer_panel'):
              case ('employee_panel'):
                if (e.data.am_cabinet_appointments) {
                  shortCodeString += ' appointments=1'
                }

                if (e.data.am_cabinet_events) {
                  shortCodeString += ' events=1'
                }

                if (e.data.am_cabinet_profile && view !== 'customer_panel') {
                  shortCodeString += ' profile-hidden=1'
                }

                if (e.data.am_trigger) {
                  shortCodeString += ' trigger=' + e.data.am_trigger
                }

                editor.insertContent(view === 'customer_panel' ? '[ameliacustomerpanel' + shortCodeString + ']' : '[ameliaemployeepanel' + shortCodeString + ']')

                break
            }
          },
          onOpen: function () {
            categoryElement = win.find('#am_category')
            serviceElement = win.find('#am_service')
            packageElement = win.find('#am_package')

            categoryElement.visible(false)
            serviceElement.visible(false)
            packageElement.visible(false)
          },
        })
      }

      // Add new button
      editor.addButton('ameliaButton', {
        title: wpAmeliaLabels.insert_amelia_shortcode,
        cmd: 'ameliaButtonCommand',
        image: window.wpAmeliaPluginURL + 'public/img/amelia-logo-admin-icon.svg'
      })

      // Button functionality
      editor.addCommand('ameliaButtonCommand', function () {
        jQuery.ajax({
          url: ajaxurl + '?action=wpamelia_api&call=/entities&types[]=categories&types[]=employees&types[]=locations&types[]=events&types[]=tags&types[]=packages',
          dataType: 'json',
          success: function (response) {
            entities = response.data
            categories = []
            services = []
            employees = []
            locations = []
            packages = []
            servicesList = []
            events = []
            tags = []

            for (let i = 0; i < response.data.categories.length; i++) {
              categories.push({
                value: response.data.categories[i].id,
                text: response.data.categories[i].name + ' (id: ' + response.data.categories[i].id + ')'
              })
            }

            // Add all services to one array
            response.data.categories.map(category => category.serviceList).forEach(function (serviceList) {
              servicesList = servicesList.concat(serviceList)
            })

            // Create array of services objects
            for (let i = 0; i < servicesList.length; i++) {
              if (servicesList[i].show) {
                services.push({
                  value: servicesList[i].id,
                  text: servicesList[i].name + ' (id: ' + servicesList[i].id + ')'
                })
              }
            }

            // Create array of packages objects
            for (let i = 0; i < response.data.categories.length; i++) {
              if (response.data.categories[i].show) {
                packages.push({
                  value: response.data.categories[i].id,
                  text: response.data.categories[i].name + ' (id: ' + response.data.categories[i].id + ')'
                })
              }
            }

            // Create array of employees objects
            for (let i = 0; i < response.data.employees.length; i++) {
              employees.push({
                value: response.data.employees[i].id,
                text: response.data.employees[i].firstName + ' ' + response.data.employees[i].lastName + ' (id: ' + response.data.employees[i].id + ')'
              })
            }

            // Create array of locations objects
            for (let i = 0; i < response.data.locations.length; i++) {
              locations.push({
                value: response.data.locations[i].id,
                text: response.data.locations[i].name + ' (id: ' + response.data.locations[i].id + ')'
              })
            }

            // Create array of packages objects
            for (let i = 0; i < response.data.packages.length; i++) {
              packages.push({
                value: response.data.packages[i].id,
                text: response.data.packages[i].name + ' (id: ' + response.data.packages[i].id + ')'
              })
            }

            for (let i = 0; i < response.data.events.length; i++) {
              events.push({
                value: response.data.events[i].id,
                text: response.data.events[i].name + ' (id: ' + response.data.events[i].id + ') - ' + response.data.events[i].formattedPeriodStart
              })
            }

            for (let i = 0; i < response.data.tags.length; i++) {
              tags.push({
                value: response.data.tags[i].name,
                text: response.data.tags[i].name
              })
            }

            // set and open editor
            setAndOpenEditor('stepbooking')
          }
        })
      })
    }
  })

  tinymce.PluginManager.add('ameliaBookingPlugin', tinymce.plugins.ameliaBookingPlugin)
})()
