(function (wp) {

  var el = wp.element.createElement
  var components = wp.components
  var blockControls = wp.editor.BlockControls
  var inspectorControls = wp.editor.InspectorControls
  var data = wpAmeliaLabels.data

  var categories = []
  var services = []
  var employees = []
  var locations = []
  var packages = []

  var blockStyle = {
    color: 'red'
  }

  if (data.categories.length !== 0) {
    for (let i = 0; i < data.categories.length; i++) {
      categories.push({
        value: data.categories[i].id,
        text: data.categories[i].name + ' (id: ' + data.categories[i].id + ')'
      })
    }
  } else {
    categories = []
  }

  if (data.servicesList.length !== 0) {
    // Create array of services objects
    for (let i = 0; i < data.servicesList.length; i++) {
      if (data.servicesList[i].length !== 0) {
        services.push({
          value: data.servicesList[i].id,
          text: data.servicesList[i].name + ' (id: ' + data.servicesList[i].id + ')'
        })
      }
    }
  } else {
    services = []
  }

  if (data.employees.length !== 0) {
    // Create array of employees objects
    for (let i = 0; i < data.employees.length; i++) {
      employees.push({
        value: data.employees[i].id,
        text: data.employees[i].firstName + ' ' + data.employees[i].lastName + ' (id: ' + data.employees[i].id + ')'
      })
    }
  } else {
    employees = []
  }

  if (data.locations.length !== 0) {
    // Create array of locations objects
    for (let i = 0; i < data.locations.length; i++) {
      locations.push({
        value: data.locations[i].id,
        text: data.locations[i].name + ' (id: ' + data.locations[i].id + ')'
      })
    }
  } else {
    locations = []
  }

  if (data.packages.length !== 0) {
    // Create array of packages objects
    for (let i = 0; i < data.packages.length; i++) {
      packages.push({
        value: data.packages[i].id,
        text: data.packages[i].name + ' (id: ' + data.packages[i].id + ')'
      })
    }
  } else {
    packages = []
  }

  // Registering the Block for booking shotcode
  wp.blocks.registerBlockType('amelia/step-booking-gutenberg-block', {
    title: wpAmeliaLabels.step_booking_gutenberg_block.title,
    description: wpAmeliaLabels.step_booking_gutenberg_block.description,
    icon: el('svg', {width: '44', height: '27', viewBox: '0 0 44 27', class: 'amelia-step-booking-gutenberg'},
      el('path', {
        style: {fill: '#1A84EE'},
        d: 'M11.5035 10.8582V2.03134C11.5035 0.469951 9.84276 -0.505952 8.5142 0.274788L0.996474 4.69241C0.379869 5.05468 3.05176e-05 5.72434 3.05176e-05 6.44897V15.2339C3.05176e-05 16.7916 1.65364 17.768 2.98221 16.9947L10.5 12.6191C11.1206 12.2578 11.5035 11.5859 11.5035 10.8582Z'
      }),
      el('path', {
        style: {fill: '#005AEE'},
        d: 'M13.4964 2.03138V10.8583C13.4964 11.5859 13.8794 12.2578 14.4999 12.619L22.0178 16.9947C23.3464 17.768 25 16.7917 25 15.2339V6.449C25 5.72438 24.6202 5.05472 24.0036 4.69245L16.4858 0.274738C15.1572 -0.505914 13.4964 0.46999 13.4964 2.03138Z'
      }),
      el('path', {
        style: {fill: '#3BA6FF'},
        d: 'M11.5107 14.3675L3.94995 18.7682C2.61502 19.5451 2.61109 21.5029 3.9428 22.2855L11.5035 26.7284C12.1201 27.0907 12.8798 27.0907 13.4964 26.7284L21.0572 22.2855C22.3889 21.5029 22.385 19.5451 21.0501 18.7682L13.4894 14.3675C12.8763 14.0106 12.1236 14.0106 11.5107 14.3675Z'
      }),
      el('rect', {
        style: {fill: '#E6EFFD'},
        x:"18", width:"26", height:"12", rx:"6"
      }),
      el('path', {
        style: {fill: '#005AEE'},
        d: 'M21.6392 9V3.18182H23.7699C24.1828 3.18182 24.5246 3.25 24.7955 3.38636C25.0663 3.52083 25.2689 3.7036 25.4034 3.93466C25.5379 4.16383 25.6051 4.42235 25.6051 4.71023C25.6051 4.95265 25.5606 5.1572 25.4716 5.32386C25.3826 5.48864 25.2633 5.62121 25.1136 5.72159C24.9659 5.82008 24.803 5.89205 24.625 5.9375V5.99432C24.8182 6.00379 25.0066 6.06629 25.1903 6.18182C25.3759 6.29545 25.5294 6.45739 25.6506 6.66761C25.7718 6.87784 25.8324 7.13352 25.8324 7.43466C25.8324 7.73201 25.7623 7.99905 25.6222 8.2358C25.4839 8.47064 25.2699 8.6572 24.9801 8.79545C24.6903 8.93182 24.3201 9 23.8693 9H21.6392ZM22.517 8.24716H23.7841C24.2045 8.24716 24.5057 8.16572 24.6875 8.00284C24.8693 7.83996 24.9602 7.63636 24.9602 7.39205C24.9602 7.20833 24.9138 7.03977 24.821 6.88636C24.7282 6.73295 24.5956 6.6108 24.4233 6.51989C24.2528 6.42898 24.0502 6.38352 23.8153 6.38352H22.517V8.24716ZM22.517 5.69886H23.6932C23.8902 5.69886 24.0672 5.66098 24.2244 5.58523C24.3835 5.50947 24.5095 5.40341 24.6023 5.26705C24.697 5.12879 24.7443 4.96591 24.7443 4.77841C24.7443 4.53788 24.66 4.33617 24.4915 4.1733C24.3229 4.01042 24.0644 3.92898 23.7159 3.92898H22.517V5.69886ZM26.8736 9V3.18182H30.5213V3.9375H27.7514V5.71023H30.331V6.46307H27.7514V8.24432H30.5554V9H26.8736ZM31.407 3.9375V3.18182H35.9098V3.9375H34.0945V9H33.2195V3.9375H31.407ZM36.7088 9H35.777L37.8707 3.18182H38.8849L40.9787 9H40.0469L38.402 4.23864H38.3565L36.7088 9ZM36.8651 6.72159H39.8878V7.46023H36.8651V6.72159Z'
      })
    ),
    category: 'amelia-blocks',
    keywords: [
      'amelia',
      'booking'
    ],
    supports: {
      customClassName: false,
      html: false
    },
    attributes: {
      short_code: {
        type: 'string',
        default: '[ameliastepbooking]'
      },
      trigger: {
        type: 'string',
        default: ''
      },
      show: {
        type: 'string',
        default: ''
      },
      location: {
        type: 'string',
        default: ''
      },
      category: {
        type: 'string',
        default: ''
      },
      service: {
        type: 'string',
        default: ''
      },
      employee: {
        type: 'string',
        default: ''
      },
      parametars: {
        type: 'boolean',
        default: false
      }
    },
    edit: function (props) {
      var inspectorElements = []
      var attributes = props.attributes
      var options = []

      options['categories'] = [{value: '', label: wpAmeliaLabels.show_all_categories}]
      options['services'] = [{value: '', label: wpAmeliaLabels.show_all_services}]
      options['employees'] = [{value: '', label: wpAmeliaLabels.show_all_employees}]
      options['locations'] = [{value: '', label: wpAmeliaLabels.show_all_locations}]
      options['show'] = [{value: '', label: wpAmeliaLabels.show_all}, {value: 'services', label: wpAmeliaLabels.services}, {value: 'packages', label: wpAmeliaLabels.packages}]

      function getOptions(data) {
        var options = []

        data = Object.keys(data).map(function (key) {
          return data[key]
        })

        data.sort(function (a, b) {
          if (parseInt(a.pos) < parseInt(b.pos)) return -1
          if (parseInt(a.pos) > parseInt(b.pos)) return 1
          return 0
        })

        data.forEach(function (element) {
          options.push({value: element.value, label: element.text})
        })

        return options
      }

      getOptions(categories)
      .forEach(function (element) {
        options['categories'].push(element)
      })

      getOptions(services)
      .forEach(function (element) {
        options['services'].push(element)
      })

      getOptions(employees)
      .forEach(function (element) {
        options['employees'].push(element)
      })

      if (locations.length) {
        getOptions(locations)
        .forEach(function (element) {
          options['locations'].push(element)
        })
      }

      function getShortCode(props, attributes) {
        var shortCode = ''
        if (categories.length !== 0 && services.length !== 0 && employees.length !== 0) {
          if (attributes.parametars) {
            shortCode = '[ameliastepbooking'

            if (attributes.show) {
              shortCode += ' show=' + attributes.show + ''
            }

            if (attributes.trigger) {
              shortCode += ' trigger=' + attributes.trigger + ''
            }

            if (attributes.service) {
              shortCode += ' service=' + attributes.service + ''
            } else if (attributes.category) {
              shortCode += ' category=' + attributes.category + ''
            }

            if (attributes.employee) {
              shortCode += ' employee=' + attributes.employee + ''
            }

            if (attributes.location) {
              shortCode += ' location=' + attributes.location + ''
            }
            shortCode += ']'
          } else {
            shortCode = '[ameliastepbooking]'
          }
        } else {
          shortCode = "Notice: Please create category, service and employee first."
        }

        props.setAttributes({short_code: shortCode})

        return shortCode
      }

      if (categories.length !== 0 && services.length !== 0 && employees.length !== 0) {

        inspectorElements.push(el(components.PanelRow,
          {},
          el('label', {htmlFor: 'amelia-js-parametars'}, wpAmeliaLabels.filter),
          el(components.FormToggle, {
            id: 'amelia-js-parametars',
            checked: attributes.parametars,
            onChange: function () {
              return props.setAttributes({parametars: !props.attributes.parametars})
            },
          })
        ))

        inspectorElements.push(el('div', {style: {'margin-bottom': '1em'}}, ''))

        if (attributes.parametars) {

          inspectorElements.push(el(components.SelectControl, {
            id: 'amelia-js-select-category',
            label: wpAmeliaLabels.select_category,
            value: attributes.category,
            options: options.categories,
            onChange: function (selectControl) {
              return props.setAttributes({category: selectControl})
            }
          }))

          inspectorElements.push(el('div', {style: {'margin-bottom': '1em'}}, ''))

          inspectorElements.push(el(components.SelectControl, {
            id: 'amelia-js-select-service',
            label: wpAmeliaLabels.select_service,
            value: attributes.service,
            options: options.services,
            onChange: function (selectControl) {
              return props.setAttributes({service: selectControl})
            }
          }))

          inspectorElements.push(el('div', {style: {'margin-bottom': '1em'}}, ''))

          inspectorElements.push(el(components.SelectControl, {
            id: 'amelia-js-select-employee',
            label: wpAmeliaLabels.select_employee,
            value: attributes.employee,
            options: options.employees,
            onChange: function (selectControl) {
              return props.setAttributes({employee: selectControl})
            }
          }))

          inspectorElements.push(el('div', {style: {'margin-bottom': '1em'}}, ''))

          inspectorElements.push(el(components.SelectControl, {
            id: 'amelia-js-select-location',
            label: wpAmeliaLabels.select_location,
            value: attributes.location,
            options: options.locations,
            onChange: function (selectControl) {
              return props.setAttributes({location: selectControl})
            }
          }))

          inspectorElements.push(el('div', {style: {'margin-bottom': '1em'}}, ''))

          inspectorElements.push(el(components.TextControl, {
            id: 'amelia-js-trigger',
            label: wpAmeliaLabels.manually_loading,
            value: attributes.trigger,
            help: wpAmeliaLabels.manually_loading_description,
            onChange: function (TextControl) {
              return props.setAttributes({trigger: TextControl})
            }
          }))

          if (packages.length) {
            inspectorElements.push(el('div', {style: {'margin-bottom': '1em'}}, ''))

            inspectorElements.push(el(components.SelectControl, {
              id: 'amelia-js-select-type',
              label: wpAmeliaLabels.show_all,
              value: attributes.show,
              options: options.show,
              onChange: function (selectControl) {
                return props.setAttributes({show: selectControl})
              }
            }))
          }
        }

        return [
          el(blockControls, {key: 'controls'}),
          el(inspectorControls, {key: 'inspector'},
            el(components.PanelBody, {initialOpen: true},
              inspectorElements
            )
          ),
          el('div', {},
            getShortCode(props, props.attributes)
          )
        ]

      } else {
        inspectorElements.push(el('p', {style: {'margin-bottom': '1em'}}, 'Please create category, services and employee first. You can find instructions in our documentation on link below.'));
        inspectorElements.push(el('a', {href:'https://wpamelia.com/quickstart/', target:'_blank', style: {'margin-bottom': '1em'}}, 'Start working with Amelia WordPress Appointment Booking plugin'));

        return [
          el(blockControls, {key: 'controls'}),
          el(inspectorControls, {key: 'inspector'},
            el(components.PanelBody, {initialOpen: true},
              inspectorElements
            )
          ),
          el('div',
            {style: blockStyle},
            getShortCode(props, props.attributes)
          )
        ]
      }

    },

    save: function (props) {
      return (
        el('div', {},
          props.attributes.short_code
        )
      )
    }
  })

})(
  window.wp
)
