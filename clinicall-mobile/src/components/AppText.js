import React from 'react';
import { Text } from 'react-native';

const AppText = ({ children, ...props }) => {
  return <Text {...props}>{children}</Text>;
};

export default AppText;