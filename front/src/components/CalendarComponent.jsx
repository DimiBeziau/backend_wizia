import dayjs from 'dayjs';
import 'dayjs/locale/zh-cn';
import { Calendar, Flex, Radio, Select, theme, Typography, ConfigProvider } from "antd";
import dayLocaleData from 'dayjs/plugin/localeData';
import { useState } from 'react';
import RadioCalendar from './ui/RadioCalendar';
import { WeeklyCalendar } from 'antd-weekly-calendar';
dayjs.extend(dayLocaleData);
dayjs.locale('fr');

function CalendarComponent() {
  const [type, setType] = useState('mois');
  const [events, setEvents] = useState([]);
  const onPanelChange = (value, mode) => {
    console.log(value.format('YYYY-MM-DD'), mode);
  };

  return (
    <>
      <RadioCalendar value={type} value1="mois" value2="semaine" onChange={setType} />
      <ConfigProvider
        theme={{
          token: {
            // Seed Token
            fullBg: 'var(--color-default-bg-color)',
            colorBgContainer: 'var(--color-default-bg-color)',
            colorText: 'var(--color-default-white)',
            colorIconHover: 'var(--color-default-white)',
            colorPrimary: '#2CD8D5FF',
            colorSplit: 'var(--color-default-grey-3)',
            colorTextDisabled: 'var(--color-default-grey-5)',
            colorBorder: 'var(--color-default-bg-color)',
            lineType: 'none'
          },
        }}
      >
        {type === 'mois' ? (
          <Calendar
            fullscreen={false}
            headerRender={({ value, type, onChange, onTypeChange }) => {
              const year = value.year();
              const month = value.month();
              const yearOptions = Array.from({ length: 20 }, (_, i) => {
                const label = year - 10 + i;
                return { label, value: label };
              });
              const monthOptions = value
                .localeData()
                .monthsShort()
                .map((label, index) => ({
                  label,
                  value: index,
                }));
              return (
                <div style={{ padding: 8 }}>

                  <Flex gap={8}>
                    <Radio.Group size="small" onChange={e => onTypeChange(e.target.value)} value={type}>
                      <Radio.Button value="month">Mois</Radio.Button>
                      <Radio.Button value="year">semaines</Radio.Button>
                    </Radio.Group>
                    <Select
                      size="small"
                      popupMatchSelectWidth={false}
                      value={year}
                      options={yearOptions}
                      onChange={newYear => {
                        const now = value.clone().year(newYear);
                        onChange(now);
                      }}
                    />
                    <Select
                      size="small"
                      popupMatchSelectWidth={false}
                      value={month}
                      options={monthOptions}
                      onChange={newMonth => {
                        const now = value.clone().month(newMonth);
                        onChange(now);
                      }}
                    />
                  </Flex>
                </div>
              );
            }}
            onPanelChange={onPanelChange}
          />) : (
          <WeeklyCalendar
            events={events}
            onEventClick={(event) => console.log(event)}
            onSelectDate={(date) => console.log(date)}
          />
        )}
      </ConfigProvider>
    </>
  );
}

export default CalendarComponent;