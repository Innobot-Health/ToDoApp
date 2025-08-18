import { mount } from '@vue/test-utils';
import Login from '@/components/Login.vue';
import axios from 'axios';

vi.mock('axios');

test('logs in successfully', async () => {
  axios.post.mockResolvedValue({ data: { token: 'fake-token' } });

  const wrapper = mount(Login);

  await wrapper.find('input[type="email"]').setValue('test@example.com');
  await wrapper.find('input[type="password"]').setValue('password');
  await wrapper.find('form').trigger('submit.prevent');

  expect(localStorage.getItem('token')).toBe('fake-token');
  expect(wrapper.emitted('login-success')).toBeTruthy();
});

test('shows error on invalid credentials', async () => {
  axios.post.mockRejectedValue({ response: { data: { message: 'Invalid credentials' } } });

  const wrapper = mount(Login);

  await wrapper.find('form').trigger('submit.prevent');

  expect(wrapper.text()).toContain('Invalid credentials');
});
