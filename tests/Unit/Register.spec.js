import Register from '@/components/Register.vue';
import axios from 'axios';
vi.mock('axios');

test('registers successfully', async () => {
  axios.post.mockResolvedValue({ data: { token: 'fake-token' } });
  
  const wrapper = mount(Register);
  await wrapper.find('form').trigger('submit.prevent');

  expect(wrapper.emitted('login-success')).toBeTruthy();
});

test('shows error on failed registration', async () => {
  axios.post.mockRejectedValue({ response: { data: { message: 'Email exists' } } });

  const wrapper = mount(Register);
  await wrapper.find('form').trigger('submit.prevent');

  expect(wrapper.vm.error).toBeTruthy();
  expect(wrapper.vm.error).toBe('Email exists');
});
